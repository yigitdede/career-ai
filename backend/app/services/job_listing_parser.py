"""İş ilanı URL parse yardımcıları."""

from __future__ import annotations

import re
from html.parser import HTMLParser
from urllib.parse import urlparse

import httpx


class _TitleParser(HTMLParser):
    def __init__(self) -> None:
        super().__init__()
        self._in_title = False
        self.title_parts: list[str] = []
        self.meta: dict[str, str] = {}

    def handle_starttag(self, tag: str, attrs: list[tuple[str, str | None]]) -> None:
        attrs_dict = {key.lower(): value or "" for key, value in attrs}
        if tag.lower() == "title":
            self._in_title = True
        if tag.lower() == "meta":
            name = (attrs_dict.get("property") or attrs_dict.get("name") or "").lower()
            content = attrs_dict.get("content") or ""
            if name and content:
                self.meta[name] = content

    def handle_endtag(self, tag: str) -> None:
        if tag.lower() == "title":
            self._in_title = False

    def handle_data(self, data: str) -> None:
        if self._in_title and data.strip():
            self.title_parts.append(data.strip())


def parse_job_listing(url: str) -> dict:
    normalized = _normalize_url(url)
    fetched_title = _fetch_title(normalized)
    title = fetched_title or _title_from_url(normalized)
    host = urlparse(normalized).netloc.replace("www.", "") or "ilan"

    return {
        "url": normalized,
        "title": title,
        "company": _company_from_host(host),
        "source": host,
        "role_id": "job-" + _slug(host + "-" + title),
        "required_skills": _skills_from_text(title + " " + normalized),
        "parsed_from": "html" if fetched_title else "url",
    }


def _normalize_url(url: str) -> str:
    clean = url.strip()
    if not clean:
        raise ValueError("URL boş olamaz")
    if not clean.lower().startswith(("http://", "https://")):
        clean = "https://" + clean
    parsed = urlparse(clean)
    if not parsed.netloc or "." not in parsed.netloc:
        raise ValueError("Geçerli bir ilan linki girin")
    return clean


def _fetch_title(url: str) -> str | None:
    try:
        response = httpx.get(
            url,
            follow_redirects=True,
            timeout=6,
            headers={"User-Agent": "Mozilla/5.0 CareerTalentAI/1.0"},
        )
        if response.status_code >= 400:
            return None
    except httpx.HTTPError:
        return None

    parser = _TitleParser()
    parser.feed(response.text[:200_000])
    raw_title = parser.meta.get("og:title") or parser.meta.get("twitter:title") or " ".join(parser.title_parts)
    return _clean_title(raw_title) if raw_title else None


def _title_from_url(url: str) -> str:
    parsed = urlparse(url)
    parts = [part for part in parsed.path.split("/") if part]
    candidate = parts[-1] if parts else parsed.netloc
    candidate = re.sub(r"\d{4,}$", "", candidate)
    return _clean_title(candidate.replace("-", " ").replace("_", " ")) or "İş ilanı"


def _clean_title(title: str) -> str:
    title = re.sub(r"\s+", " ", title).strip(" -|•\t\n\r")
    for sep in [" | ", " - ", " — ", " – "]:
        if sep in title:
            title = title.split(sep)[0].strip()
    return title[:120]


def _company_from_host(host: str) -> str:
    first = host.split(".")[0]
    return first.replace("-", " ").title()


def _skills_from_text(text: str) -> list[str]:
    catalog = [
        "SQL", "Python", "Excel", "Power BI", "Tableau", "Pandas", "React", "JavaScript",
        "TypeScript", "Docker", "PostgreSQL", "FastAPI", "Django", "Git", "Agile", "Scrum",
        "İletişim", "Veri Görselleştirme", "REST API", "Scikit-learn",
    ]
    lower = text.lower()
    found = [skill for skill in catalog if skill.lower() in lower]
    return found or ["Rol gereksinimleri", "CV anahtar kelimeleri", "Portfolio kanıtı"]


def _slug(value: str) -> str:
    slug = re.sub(r"[^a-zA-Z0-9]+", "-", value.lower()).strip("-")
    return slug[:80] or "target"
