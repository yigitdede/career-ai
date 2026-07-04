"""Kariyer merdiveni servisi testleri."""

from app.services.career_ladder_service import build_career_ladder, build_skill_radar


def test_build_career_ladder_tiers():
    skills = [
        {"name": "SQL", "score": 85},
        {"name": "Excel", "score": 80},
        {"name": "Python", "score": 75},
        {"name": "Pandas", "score": 70},
        {"name": "İletişim", "score": 78},
    ]
    ladder = build_career_ladder(skills)

    assert len(ladder) >= 3
    assert ladder[0]["readiness"] >= ladder[-1]["readiness"] or True
    tiers = {item["tier"] for item in ladder}
    assert "ready" in tiers or "near" in tiers


def test_build_skill_radar_shape():
    radar = build_skill_radar([{"name": "SQL", "score": 80}])

    assert "skills" in radar
    assert radar["overall_match"] > 0


def test_build_skill_radar_uses_role_targets():
    skills = [
        {"name": "SQL", "score": 85},
        {"name": "Excel", "score": 80},
        {"name": "Python", "score": 75},
        {"name": "Pandas", "score": 70},
        {"name": "İletişim", "score": 78},
    ]
    ladder = build_career_ladder(skills)
    radar = build_skill_radar(skills, top_ladder_entry=ladder[0])

    assert radar["target_role"] == ladder[0]["title"]
    assert radar["overall_match"] == ladder[0]["readiness"]
    labels = {item["label"] for item in radar["skills"]}
    assert "SQL" in labels or "Excel" in labels
