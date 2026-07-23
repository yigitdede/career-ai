from collections.abc import Generator

import pytest
from sqlalchemy import create_engine
from sqlalchemy.orm import Session, sessionmaker
from sqlalchemy.pool import StaticPool

from app.core.database import Base
from app.models.career_engine import CareerAnalysis
from app.models.engagement import CvDocument
from app.schemas.career import CvBuilderDraftAI
from app.services import cv_builder_import as service


@pytest.fixture()
def db() -> Generator[Session, None, None]:
    engine = create_engine("sqlite://", connect_args={"check_same_thread": False}, poolclass=StaticPool)
    Base.metadata.create_all(engine)
    session = sessionmaker(bind=engine)()
    try:
        yield session
    finally:
        session.close()
        Base.metadata.drop_all(engine)


def _source(db: Session) -> tuple[CvDocument, CareerAnalysis]:
    document = CvDocument(
        id="uploaded-cv-1",
        user_id=1,
        kind="uploaded",
        display_name="Buse Batan.pdf",
        original_name="Buse Batan.pdf",
        file_path="/tmp/uploaded-cv-1.pdf",
        file_size=123,
        is_current=True,
    )
    analysis = CareerAnalysis(
        id="analysis-1",
        user_id=1,
        cv_document_id=document.id,
        status="ready",
        source="upload",
        file_name=document.original_name,
        cv_text="Buse Batan; Veri Analisti; SQL ve Python; İstanbul.",
    )
    db.add_all([document, analysis])
    db.commit()
    return document, analysis


def _draft(language: str) -> CvBuilderDraftAI:
    return CvBuilderDraftAI(
        personal={
            "full_name": f"{language} ad",
            "email": "",
            "phone": "",
            "location": "İstanbul",
            "linkedin": "",
            "summary": "Veri analisti",
        },
        education=[],
        experience=[{"organization": "Acme", "title": "Analist", "bullets": ["SQL kullandı"]}],
        skills=[{"category": "Teknik", "items": "SQL"}],
        projects=[],
        certificates=[],
    )


def test_import_creates_bilingual_editor_payload_with_lineage(db, monkeypatch):
    document, analysis = _source(db)
    languages: list[str] = []

    def fake_invoke(_prompt, schema, language="tr"):
        assert schema is CvBuilderDraftAI
        languages.append(language)
        return _draft(language)

    monkeypatch.setattr(service, "_invoke", fake_invoke)

    result = service.import_cv_to_builder(db, document, analysis)

    assert languages == ["tr", "en"]
    assert result.builder_draft_status == "ready"
    assert result.builder_draft_error is None
    assert result.builder_draft_analysis_id == analysis.id
    assert result.is_current is True
    assert result.builder_data["tr"]["personal"]["full_name"] == "tr ad"
    assert result.builder_data["en"]["personal"]["full_name"] == "en ad"
    assert result.builder_data["tr"]["experience"][0]["id"]
    assert result.builder_data["tr"]["enabledOptional"] == []
    assert result.builder_data["tr"]["optional"] == {}
    assert result.builder_data["_meta"] == {
        "source_document_id": document.id,
        "source_analysis_id": analysis.id,
        "source_file_name": "Buse Batan.pdf",
        "missing_fields": {
            "tr": ["personal.email", "personal.phone", "personal.linkedin", "education", "experience", "projects", "certificates"],
            "en": ["personal.email", "personal.phone", "personal.linkedin", "education", "experience", "projects", "certificates"],
        },
    }


def test_import_preserves_empty_values_and_rows_are_independently_identified(db, monkeypatch):
    document, analysis = _source(db)
    monkeypatch.setattr(service, "_invoke", lambda _prompt, _schema, language="tr": _draft(language))

    service.import_cv_to_builder(db, document, analysis)
    payload = document.builder_data

    assert payload["tr"]["personal"]["email"] == ""
    assert payload["tr"]["personal"]["linkedin"] == ""
    assert payload["tr"]["education"] == []
    assert payload["tr"]["projects"] == []
    assert payload["tr"]["certificates"] == []
    assert payload["tr"]["experience"][0]["id"] != payload["en"]["experience"][0]["id"]


def test_import_failure_marks_draft_failed_without_touching_uploaded_document(db, monkeypatch):
    document, analysis = _source(db)
    old_data = {"legacy": "keep"}
    document.builder_data = old_data
    db.commit()

    def unavailable(*_args, **_kwargs):
        raise RuntimeError("provider unavailable")

    monkeypatch.setattr(service, "_invoke", unavailable)

    with pytest.raises(RuntimeError, match="provider unavailable"):
        service.import_cv_to_builder(db, document, analysis)

    db.expire_all()
    persisted = db.get(CvDocument, document.id)
    assert persisted.builder_draft_status == "failed"
    assert persisted.builder_draft_error == "CV alanları AI ile hazırlanamadı. Lütfen tekrar deneyin."
    assert persisted.builder_data == old_data
    assert persisted.file_path == "/tmp/uploaded-cv-1.pdf"
    assert persisted.is_current is True
