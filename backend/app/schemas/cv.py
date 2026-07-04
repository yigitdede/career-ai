"""CV analiz istek şemaları."""

from __future__ import annotations

from pydantic import BaseModel, Field


class AnalyzeTextRequest(BaseModel):
    cv_text: str = Field(..., min_length=40)
    file_name: str | None = None
