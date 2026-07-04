"""PDF CV metin çıkarımı."""

from __future__ import annotations

import io

import pdfplumber


def extract_text_from_pdf(data: bytes) -> str:
    chunks: list[str] = []

    with pdfplumber.open(io.BytesIO(data)) as pdf:
        for page in pdf.pages:
            text = page.extract_text() or ""
            if text.strip():
                chunks.append(text.strip())

    return "\n\n".join(chunks).strip()
