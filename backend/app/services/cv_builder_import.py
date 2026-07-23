"""AI destekli dış CV -> CV Merkezi alanları aktarımı."""

from __future__ import annotations

import json
from typing import Any
from uuid import uuid4

from sqlalchemy.orm import Session

from app.models.career_engine import CareerAnalysis
from app.models.engagement import CvDocument
from app.schemas.career import CvBuilderDraftAI
from app.services.career_engine import _invoke


_BUILDER_SECTIONS = ("education", "experience", "skills", "projects", "certificates")
_PERSONAL_FIELDS = ("full_name", "email", "phone", "location", "linkedin", "summary")


def _source_prompt(document: CvDocument, analysis: CareerAnalysis, language: str) -> dict[str, Any]:
    """Build the extraction contract sent to the career engine."""

    return {
        "purpose": "Dışarıdan yüklenen CV metnini CV Merkezi düzenleyici alanlarına aktar",
        "output_language": language,
        "rules": [
            "Yalnızca kaynak CV metninde açıkça bulunan gerçekleri kullan",
            "Kişi, kurum, tarih, eğitim, deneyim, proje, sertifika, iletişim bilgisi ve beceri uydurma",
            "Kaynakta bulunmayan başarı, sayı, görev, süre, teknoloji veya iletişim bilgisi ekleme",
            "Belirsiz veya eksik alanları boş string, boş liste ya da boş nesne olarak bırak",
            "Kaynakta olmayan bir alanı başka bir alandan tahmin ederek doldurma",
            "Tüm alanları CV Merkezi kutucuklarına uygun şekilde döndür",
            "tr alanı Türkçe, en alanı İngilizce yaz; özel adları ve teknik terimleri koru",
        ],
        "source": {
            "file_name": document.original_name or document.display_name,
            "analysis_id": analysis.id,
            "cv_text": (analysis.cv_text or "")[:30000],
        },
    }


def _normalize_payload(output: CvBuilderDraftAI) -> tuple[dict[str, Any], list[str]]:
    payload = output.model_dump(mode="json")

    # The editor owns row identity. Never trust/generated IDs from an AI payload.
    for section in _BUILDER_SECTIONS:
        rows = payload.get(section)
        if not isinstance(rows, list):
            rows = []
        for row in rows:
            if isinstance(row, dict):
                row["id"] = str(uuid4())
        payload[section] = rows

    payload["enabledOptional"] = []
    payload["optional"] = {}

    missing: list[str] = []
    personal = payload.get("personal")
    if not isinstance(personal, dict):
        personal = {field: "" for field in _PERSONAL_FIELDS}
        payload["personal"] = personal
    for field in _PERSONAL_FIELDS:
        value = personal.get(field, "")
        if isinstance(value, str):
            if not value.strip():
                personal[field] = ""
                missing.append(f"personal.{field}")
            else:
                personal[field] = value
        elif value is None:
            personal[field] = ""
            missing.append(f"personal.{field}")

    for section in _BUILDER_SECTIONS:
        rows = payload[section]
        row_has_blank = any(
            any(
                value is None
                or (isinstance(value, str) and not value.strip())
                or (isinstance(value, list) and not value)
                for key, value in row.items()
                if key != "id"
            )
            for row in rows
            if isinstance(row, dict)
        )
        if not rows or row_has_blank:
            missing.append(section)

    return payload, missing


def import_cv_to_builder(
    db: Session,
    document: CvDocument,
    analysis: CareerAnalysis,
) -> CvDocument:
    """Extract a ready CV analysis into bilingual, editor-ready builder data.

    The document is the uploaded PDF and is deliberately never replaced or made
    current by this operation. Only its builder draft fields are changed.
    """

    if document.kind != "uploaded":
        raise ValueError("Yalnız yüklenen PDF CV oluşturucuya aktarılabilir")
    if analysis.status != "ready":
        raise ValueError("CV analizi hazır değil")
    if document.user_id != analysis.user_id:
        raise ValueError("CV belgesi ve analiz aynı kullanıcıya ait değil")
    if analysis.cv_document_id is not None and analysis.cv_document_id != document.id:
        raise ValueError("CV belgesi ve analiz eşleşmiyor")

    try:
        localized: dict[str, dict[str, Any]] = {}
        missing_fields: dict[str, list[str]] = {}
        for language in ("tr", "en"):
            output = _invoke(
                json.dumps(_source_prompt(document, analysis, language), ensure_ascii=False),
                CvBuilderDraftAI,
                language=language,
            )
            if not isinstance(output, CvBuilderDraftAI):
                output = CvBuilderDraftAI.model_validate(output)
            localized[language], missing_fields[language] = _normalize_payload(output)

        localized["_meta"] = {
            "source_document_id": document.id,
            "source_analysis_id": analysis.id,
            "source_file_name": analysis.file_name or document.original_name or document.display_name,
            "missing_fields": missing_fields,
        }
        document.builder_data = localized
        document.builder_draft_analysis_id = analysis.id
        document.builder_draft_status = "ready"
        document.builder_draft_error = None
        db.commit()
        db.refresh(document)
        return document
    except Exception as exc:
        # Keep the uploaded PDF and its active/current flags untouched. Persist
        # only draft state so a later retry can use the same source safely.
        document.builder_draft_status = "failed"
        document.builder_draft_error = "CV alanları AI ile hazırlanamadı. Lütfen tekrar deneyin."
        db.commit()
        raise
