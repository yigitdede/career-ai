"""Demo panel hedef rol kalıcı store'u.

Auth gelene kadar tek demo kullanıcı için JSON dosya kullanır. DB'ye taşınacak yüzey bu servis.
"""

from __future__ import annotations

import json
from pathlib import Path
from threading import Lock
from typing import Any

REPO_ROOT = Path(__file__).resolve().parents[3]
STORE_FILE = REPO_ROOT / "data" / "panel_targets.json"
_DEMO_USER_ID = "demo"
_LOCK = Lock()


def get_target(user_id: str = _DEMO_USER_ID) -> dict[str, Any] | None:
    payload = _read()
    target = payload.get(user_id)
    return target if isinstance(target, dict) else None


def put_target(target: dict[str, Any], user_id: str = _DEMO_USER_ID) -> dict[str, Any]:
    payload = _read()
    payload[user_id] = target
    _write(payload)
    return target


def _read() -> dict[str, Any]:
    with _LOCK:
        if not STORE_FILE.is_file():
            return {}
        try:
            payload = json.loads(STORE_FILE.read_text(encoding="utf-8"))
        except json.JSONDecodeError:
            return {}
        return payload if isinstance(payload, dict) else {}


def _write(payload: dict[str, Any]) -> None:
    with _LOCK:
        STORE_FILE.parent.mkdir(parents=True, exist_ok=True)
        STORE_FILE.write_text(json.dumps(payload, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
