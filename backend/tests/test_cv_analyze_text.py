"""CV analyze-text endpoint testleri."""

from fastapi.testclient import TestClient

from app.main import app

client = TestClient(app)


def test_analyze_text_endpoint(monkeypatch):
    monkeypatch.setattr(
        "app.api.v1.cv.extract_profile_from_text",
        lambda text: {
            "summary": "test",
            "skills": [{"name": "SQL", "score": 80}, {"name": "Python", "score": 70}],
            "source": "ai",
        },
    )

    response = client.post(
        "/api/v1/cv/analyze-text",
        json={
            "cv_text": "Ayşe Yılmaz\nData Analyst\nSQL Python Excel experience for 2 years in analytics projects",
            "file_name": "ayse-builder.json",
        },
    )

    assert response.status_code == 200
    body = response.json()
    assert body["file_name"] == "ayse-builder.json"
    assert body["skill_radar"]["skills"]
    assert body["career_ladder"]
