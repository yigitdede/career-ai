"""CV metninden yetenek profili çıkarımı (Gemini + kural tabanlı yedek)."""

from __future__ import annotations

import json
import re
from typing import Any

from langchain_core.messages import HumanMessage, SystemMessage

from app.services.ai_factory import ai_configured, create_chat_model
from app.services.roles_catalog import load_roles

_SKILL_LEVEL_SCORES = {
    "ileri": 90,
    "orta": 70,
    "temel": 50,
    "başlangıç": 35,
    "baslangic": 35,
}


def _normalize_skill_name(name: str) -> str:
    return re.sub(r"\s+", " ", name.strip().lower())


def _fallback_skills(cv_text: str) -> list[dict[str, Any]]:
    catalog_names: list[str] = []
    for role in load_roles():
        for skill in role.get("required_skills", []):
            catalog_names.append(skill.get("name", ""))

    found: list[dict[str, Any]] = []
    lowered = cv_text.lower()

    for name in sorted(set(catalog_names), key=len, reverse=True):
        if not name:
            continue
        if name.lower() in lowered:
            found.append({"name": name, "score": 72})

    if not found:
        found = [
            {"name": "Excel", "score": 60},
            {"name": "SQL", "score": 55},
            {"name": "Python", "score": 50},
        ]

    return found[:12]


def extract_profile_from_text(cv_text: str) -> dict[str, Any]:
    """CV metninden yetenek listesi ve kısa özet döner."""
    if not cv_text.strip():
        raise ValueError("CV metni boş")

    if not ai_configured():
        skills = _fallback_skills(cv_text)
        return {
            "summary": cv_text[:280],
            "skills": skills,
            "source": "keyword_fallback",
        }

    model = create_chat_model()
    prompt = (
        "Aşağıdaki CV metninden yetenekleri çıkar. Yalnızca geçerli JSON döndür, markdown yok.\n"
        'Şema: {"summary":"...", "skills":[{"name":"...", "score":0-100}]}\n'
        "score: o yetenekteki güç (0-100). En fazla 15 yetenek.\n\n"
        f"CV:\n{cv_text[:12000]}"
    )
    response = model.invoke([
        SystemMessage(content="Sen bir CV analiz asistanısın. Yalnızca JSON üret."),
        HumanMessage(content=prompt),
    ])
    raw = (response.content or "").strip()
    raw = raw.removeprefix("```json").removeprefix("```").removesuffix("```").strip()

    try:
        payload = json.loads(raw)
        skills = payload.get("skills") or []
        normalized = []
        for item in skills:
            name = str(item.get("name", "")).strip()
            if not name:
                continue
            score = int(item.get("score", 60))
            normalized.append({"name": name, "score": max(0, min(100, score))})

        if not normalized:
            raise ValueError("Boş skills")

        return {
            "summary": str(payload.get("summary", ""))[:500],
            "skills": normalized,
            "source": "ai",
        }
    except (json.JSONDecodeError, ValueError, TypeError):
        skills = _fallback_skills(cv_text)
        return {
            "summary": cv_text[:280],
            "skills": skills,
            "source": "keyword_fallback",
        }
