"""Bootcamp meslek kataloğu — repo data/roles."""

from __future__ import annotations

import json
from functools import lru_cache
from pathlib import Path
from typing import Any

REPO_ROOT = Path(__file__).resolve().parents[3]
ROLES_FILE = REPO_ROOT / "data" / "roles" / "bootcamp_roles.json"


@lru_cache
def load_roles() -> list[dict[str, Any]]:
    if not ROLES_FILE.is_file():
        return []

    payload = json.loads(ROLES_FILE.read_text(encoding="utf-8"))

    return list(payload.get("roles", []))
