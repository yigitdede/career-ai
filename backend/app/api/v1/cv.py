"""CV analiz endpoint'leri."""

from __future__ import annotations

from fastapi import APIRouter, File, HTTPException, UploadFile

from app.core.config import settings
from app.schemas.cv import AnalyzeTextRequest
from app.services.career_ladder_service import build_career_ladder, build_skill_radar
from app.services.cv_parser import extract_text_from_pdf
from app.services.cv_profile import extract_profile_from_text

router = APIRouter()

_MAX_BYTES = settings.MAX_UPLOAD_SIZE_MB * 1024 * 1024


def _analyze_from_text(cv_text: str, file_name: str) -> dict:
    profile = extract_profile_from_text(cv_text)
    skills = profile.get("skills", [])
    ladder = build_career_ladder(skills)
    top_entry = ladder[0] if ladder else None
    radar = build_skill_radar(skills, top_ladder_entry=top_entry)

    return {
        "status": "ready",
        "file_name": file_name,
        "profile": profile,
        "skill_radar": radar,
        "career_ladder": ladder,
    }


@router.post("/analyze-text")
async def analyze_cv_text(body: AnalyzeTextRequest):
    """Oluşturucu veya düz metin CV → yetenek + kariyer merdiveni."""
    cv_text = body.cv_text.strip()
    if len(cv_text) < 40:
        raise HTTPException(status_code=422, detail="CV metni çok kısa")

    file_name = (body.file_name or "cv-builder.json").strip() or "cv-builder.json"

    return _analyze_from_text(cv_text, file_name)


@router.post("/analyze")
async def analyze_cv(file: UploadFile = File(...)):
    """
    PDF CV yükle → yetenek çıkarımı → kariyer merdiveni + radar.
    Sprint 1: senkron; auth yok (demo).
    """
    if not file.filename:
        raise HTTPException(status_code=422, detail="Dosya adı gerekli")

    if not file.filename.lower().endswith(".pdf"):
        raise HTTPException(status_code=422, detail="Yalnızca PDF kabul edilir")

    data = await file.read()
    if len(data) > _MAX_BYTES:
        raise HTTPException(status_code=413, detail="Dosya çok büyük")

    if len(data) < 32:
        raise HTTPException(status_code=422, detail="Geçersiz PDF")

    try:
        cv_text = extract_text_from_pdf(data)
    except Exception as exc:
        raise HTTPException(status_code=422, detail=f"PDF okunamadı: {exc}") from exc

    if len(cv_text) < 40:
        raise HTTPException(status_code=422, detail="PDF'den yeterli metin çıkarılamadı")

    return _analyze_from_text(cv_text, file.filename or "cv.pdf")
