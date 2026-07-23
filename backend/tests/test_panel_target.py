"""Panel ilan parse endpoint testleri."""

import pytest
from fastapi.testclient import TestClient

from app.core.security import get_current_user
from app.main import app
from app.models.user import User

client = TestClient(app)


@pytest.fixture(autouse=True)
def authenticated_panel_user():
    app.dependency_overrides[get_current_user] = lambda: User(
        id=1,
        full_name="Panel Test User",
        email="panel-user@example.test",
        hashed_password="not-used",
        is_active=True,
        is_admin=False,
    )
    yield
    app.dependency_overrides.pop(get_current_user, None)


def test_job_listing_parse_uses_html_title(monkeypatch):
    class DummyResponse:
        status_code = 200
        text = """<html><head><meta property='og:title' content='Junior Product Analyst - Acme'></head></html>"""

    monkeypatch.setattr("app.services.job_listing_parser.httpx.get", lambda *args, **kwargs: DummyResponse())

    response = client.post(
        "/api/v1/panel/job-listings/parse",
        json={"url": "https://jobs.example.com/view/junior-product-analyst-123"},
    )

    assert response.status_code == 200
    body = response.json()
    assert body["title"] == "Junior Product Analyst"
    assert body["parsed_from"] == "html"
    assert body["role_id"].startswith("job-")


def test_job_listing_parse_falls_back_to_url(monkeypatch):
    import httpx

    monkeypatch.setattr("app.services.job_listing_parser.httpx.get", lambda *args, **kwargs: (_ for _ in ()).throw(httpx.ConnectError("blocked")))

    response = client.post(
        "/api/v1/panel/job-listings/parse",
        json={"url": "https://jobs.example.com/view/senior-data-analyst-987654"},
    )

    assert response.status_code == 200
    body = response.json()
    assert body["title"] == "senior data analyst"
    assert body["parsed_from"] == "url"


def test_job_listing_parse_extracts_skills_from_html_body(monkeypatch):
    class DummyResponse:
        status_code = 200
        text = """
        <html><head><title>Product Analyst</title><meta name='description' content='Own funnel and retention metrics'></head>
        <body>We need SQL, Python, Power BI, A/B testing, cohort analysis and stakeholder communication.</body></html>
        """

    monkeypatch.setattr("app.services.job_listing_parser.httpx.get", lambda *args, **kwargs: DummyResponse())
    monkeypatch.setattr("app.services.job_listing_parser.ai_configured", lambda: False)

    response = client.post(
        "/api/v1/panel/job-listings/parse",
        json={"url": "https://jobs.example.com/product-analyst"},
    )

    assert response.status_code == 200
    body = response.json()
    assert body["title"] == "Product Analyst"
    assert body["parsed_from"] == "html"
    assert "SQL" in body["required_skills"]
    assert "Power BI" in body["required_skills"]
    assert "A/B Test" in body["required_skills"]
    assert "Product Analytics" in body["required_skills"]


def test_job_listing_parse_extracts_json_ld_job_posting(monkeypatch):
    class DummyResponse:
        status_code = 200
        text = """
        <html><head><title>Backend Developer</title></head>
        <body>
        <script type="application/ld+json">
        {"@type":"JobPosting","title":"Backend Developer","description":"FastAPI, Docker, REST API and PostgreSQL required."}
        </script>
        </body></html>
        """

    monkeypatch.setattr("app.services.job_listing_parser.httpx.get", lambda *args, **kwargs: DummyResponse())
    monkeypatch.setattr("app.services.job_listing_parser.ai_configured", lambda: False)

    response = client.post(
        "/api/v1/panel/job-listings/parse",
        json={"url": "https://jobs.example.com/backend-developer"},
    )

    assert response.status_code == 200
    body = response.json()
    assert body["title"] == "Backend Developer"
    assert body["parsed_from"] == "html"
    assert "FastAPI" in body["required_skills"]
    assert "Docker" in body["required_skills"]
    assert "REST API" in body["required_skills"]
