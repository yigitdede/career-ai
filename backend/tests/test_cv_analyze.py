"""CV analyze endpoint testleri."""

from io import BytesIO

from fastapi.testclient import TestClient

from app.main import app

client = TestClient(app)

_MINIMAL_PDF = b"""%PDF-1.4
1 0 obj<<>>endobj
2 0 obj<</Length 44>>stream
BT /F1 12 Tf 100 700 Td (SQL Python Excel) Tj ET
endstream
endobj
3 0 obj<</Type/Page/Parent 4 0 R/MediaBox[0 0 612 792]/Contents 2 0 R>>endobj
4 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj
5 0 obj<</Type/Catalog/Pages 4 0 R>>endobj
xref
0 6
0000000000 65535 f
0000000009 00000 n
0000000032 00000 n
0000000125 00000 n
0000000224 00000 n
0000000280 00000 n
trailer<</Size 6/Root 5 0 R>>
startxref
338
%%EOF"""


def test_cv_analyze_rejects_non_pdf(client):
    client.post("/api/v1/auth/register", json={"full_name": "Ayşe Yılmaz", "email": "ayse@example.com", "password": "GucluParola123!"})
    token = client.post("/api/v1/auth/login", data={"username": "ayse@example.com", "password": "GucluParola123!"}).json()["access_token"]
    response = client.post(
        "/api/v1/cv/analyze",
        files={"file": ("cv.txt", b"hello", "text/plain")},
        headers={"Authorization": f"Bearer {token}"},
    )
    assert response.status_code == 422


def test_cv_analyze_accepts_pdf_and_tracks_current_upload(client, monkeypatch, tmp_path):
    client.post("/api/v1/auth/register", json={"full_name": "Ayşe Yılmaz", "email": "ayse@example.com", "password": "GucluParola123!"})
    token = client.post("/api/v1/auth/login", data={"username": "ayse@example.com", "password": "GucluParola123!"}).json()["access_token"]
    monkeypatch.setattr(
        "app.api.v1.cv.extract_text_from_pdf",
        lambda _data: "SQL Python Excel Pandas ile veri analizi deneyimi",
    )
    monkeypatch.setattr("app.api.v1.cv.analyze_cv_task.delay", lambda _analysis_id: None)
    monkeypatch.setattr("app.api.v1.cv.settings.UPLOAD_DIR", str(tmp_path))

    response = client.post(
        "/api/v1/cv/analyze",
        files={"file": ("cv.pdf", BytesIO(_MINIMAL_PDF), "application/pdf")},
        headers={"Authorization": f"Bearer {token}"},
    )

    assert response.status_code == 202
    data = response.json()
    assert data["status"] == "queued"
    assert data["analysis_id"]
    documents = client.get("/api/v1/cv/documents", headers={"Authorization": f"Bearer {token}"}).json()
    assert len(documents) == 1
    assert documents[0]["kind"] == "uploaded"
    assert documents[0]["is_current"] is True
    first_id = documents[0]["id"]
    first_analysis = client.get(f"/api/v1/career/analysis/{data['analysis_id']}", headers={"Authorization": f"Bearer {token}"}).json()
    assert first_analysis["cv_document_id"] == first_id
    assert first_analysis["file_name"] == "cv.pdf"
    assert first_analysis["source"] == "upload"

    second = client.post(
        "/api/v1/cv/analyze",
        files={"file": ("job-specific.pdf", BytesIO(_MINIMAL_PDF), "application/pdf")},
        headers={"Authorization": f"Bearer {token}"},
    )
    assert second.status_code == 202
    documents = client.get("/api/v1/cv/documents", headers={"Authorization": f"Bearer {token}"}).json()
    assert len(documents) == 2
    assert [item["display_name"] for item in documents if item["is_current"]] == ["job-specific.pdf"]
    assert next(item for item in documents if item["id"] == first_id)["is_current"] is False
    profile = client.get("/api/v1/career/profile", headers={"Authorization": f"Bearer {token}"}).json()
    assert profile["uploaded_cv"]["name"] == "job-specific.pdf"
    current_id = next(item["id"] for item in documents if item["is_current"])
    assert client.patch(f"/api/v1/cv/documents/{current_id}/archive", headers={"Authorization": f"Bearer {token}"}).status_code == 200
    assert client.get("/api/v1/career/profile", headers={"Authorization": f"Bearer {token}"}).json()["uploaded_cv"] is None

    reanalysis = client.post(f"/api/v1/cv/documents/{first_id}/analyze", headers={"Authorization": f"Bearer {token}"})
    assert reanalysis.status_code == 202
    active = client.get(f"/api/v1/career/analysis/{reanalysis.json()['analysis_id']}", headers={"Authorization": f"Bearer {token}"}).json()
    assert active["id"] == reanalysis.json()["analysis_id"]
    assert active["cv_document_id"] == first_id
    assert active["file_name"] == "cv.pdf"
    assert active["source"] == "archive_uploaded"


def test_generated_cv_archive_can_start_owner_scoped_active_analysis(client, monkeypatch, tmp_path):
    client.post("/api/v1/auth/register", json={"full_name": "CV Owner", "email": "owner@example.com", "password": "GucluParola123!"})
    token = client.post("/api/v1/auth/login", data={"username": "owner@example.com", "password": "GucluParola123!"}).json()["access_token"]
    headers = {"Authorization": f"Bearer {token}"}
    monkeypatch.setattr("app.api.v1.cv.settings.UPLOAD_DIR", str(tmp_path))
    monkeypatch.setattr("app.api.v1.cv.analyze_cv_task.delay", lambda _analysis_id: None)

    created = client.post("/api/v1/cv/documents/generated", headers=headers, files={"file": ("ignored.pdf", BytesIO(_MINIMAL_PDF), "application/pdf")}, data={"display_name": "Trendyol Başvuru CV", "language": "tr", "builder_data": '{"tr":{"personal":{"full_name":"CV Owner","summary":"Veri analizi uzmanı"},"experience":[{"title":"Veri Analisti","details":"SQL Python Pandas ile raporlama"}],"skills":[{"category":"Teknik","items":"SQL Python Excel"}]}}'})
    assert created.status_code == 201
    document_id = created.json()["id"]
    assert created.json()["display_name"] == "Trendyol Başvuru CV.pdf"
    detail = client.get(f"/api/v1/cv/documents/{document_id}", headers=headers).json()
    assert detail["builder_data"]["tr"]["personal"]["full_name"] == "CV Owner"
    download = client.get(f"/api/v1/cv/documents/{document_id}/download", headers=headers)
    assert download.status_code == 200
    assert download.content.startswith(b"%PDF")

    queued = client.post(f"/api/v1/cv/documents/{document_id}/analyze", headers=headers)
    assert queued.status_code == 202
    assert queued.json()["status"] == "queued"
    current = client.get(f"/api/v1/career/analysis/{queued.json()['analysis_id']}", headers=headers).json()
    assert current["id"] == queued.json()["analysis_id"]
    assert current["cv_document_id"] == document_id
    assert current["file_name"] == "Trendyol Başvuru CV.pdf"
    assert current["source"] == "archive_generated"

    other = client.post("/api/v1/auth/register", json={"full_name": "Other", "email": "other-cv@example.com", "password": "GucluParola123!"})
    assert other.status_code == 201
    other_token = client.post("/api/v1/auth/login", data={"username": "other-cv@example.com", "password": "GucluParola123!"}).json()["access_token"]
    assert client.get(f"/api/v1/cv/documents/{document_id}", headers={"Authorization": f"Bearer {other_token}"}).status_code == 404
    assert client.post(f"/api/v1/cv/documents/{document_id}/analyze", headers={"Authorization": f"Bearer {other_token}"}).status_code == 404
    assert client.delete(f"/api/v1/cv/documents/{document_id}", headers=headers).status_code == 204
    assert client.get(f"/api/v1/cv/documents/{document_id}", headers=headers).status_code == 404
