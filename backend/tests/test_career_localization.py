from app.models.career_engine import CareerAnalysis, CareerTask, CareerTarget
from app.models.user import User
from app.schemas.career import (
    CareerAnalysisAI,
    CareerAnalysisLocalizationsAI,
    CareerPlanAI,
    CareerPlanLocalizationsAI,
)
from app.services import career_engine
from app.services.ai_factory import AIProviderError


def register_and_headers(client, email="locale@example.com"):
    client.post(
        "/api/v1/auth/register",
        json={"full_name": "Locale User", "email": email, "password": "GucluParola123!"},
    )
    token = client.post(
        "/api/v1/auth/login",
        data={"username": email, "password": "GucluParola123!"},
    ).json()["access_token"]
    return {"Authorization": f"Bearer {token}"}


def db_session():
    app = __import__("app.main", fromlist=["app"]).app
    get_db = __import__("app.core.database", fromlist=["get_db"]).get_db
    return next(app.dependency_overrides[get_db]())


def analysis_payload():
    return {
        "current_role": "Veri Analisti",
        "profile": {"seniority": "Orta seviye"},
        "skills": [{"name": "SQL", "score": 80}],
        "radar": [{"label": "İletişim", "score": 70, "target": 85}],
        "roles": [
            {
                "tier": "A",
                "title": "Veri Analisti",
                "readiness": 80,
                "swot": {
                    "strengths": ["Güçlü SQL"],
                    "weaknesses": ["Sunum pratiği"],
                    "opportunities": ["Fintech büyümesi"],
                    "threats": ["Yoğun rekabet"],
                },
            },
            {
                "tier": "B",
                "title": "BI Analisti",
                "readiness": 60,
                "swot": {"strengths": ["SQL"], "weaknesses": ["Power BI"], "opportunities": [], "threats": []},
            },
            {
                "tier": "C",
                "title": "Veri Bilimci",
                "readiness": 35,
                "swot": {"strengths": [], "weaknesses": ["Machine Learning"], "opportunities": [], "threats": []},
            },
        ],
    }


def analysis_localizations_payload():
    tr_roles = [
        {"title": "Veri Analisti", "strengths": ["Güçlü SQL"], "weaknesses": ["Sunum pratiği"], "opportunities": ["Fintech büyümesi"], "threats": ["Yoğun rekabet"]},
        {"title": "BI Analisti", "strengths": ["SQL"], "weaknesses": ["Power BI"], "opportunities": [], "threats": []},
        {"title": "Veri Bilimci", "strengths": [], "weaknesses": ["Machine Learning"], "opportunities": [], "threats": []},
    ]
    en_roles = [
        {"title": "Data Analyst", "strengths": ["Strong SQL"], "weaknesses": ["Presentation practice"], "opportunities": ["Fintech growth"], "threats": ["Strong competition"]},
        {"title": "BI Analyst", "strengths": ["SQL"], "weaknesses": ["Power BI"], "opportunities": [], "threats": []},
        {"title": "Data Scientist", "strengths": [], "weaknesses": ["Machine Learning"], "opportunities": [], "threats": []},
    ]
    return {
        "tr": {
            "current_role": "Veri Analisti",
            "profile": {"seniority": "Orta seviye"},
            "skill_names": ["SQL"],
            "radar_labels": ["İletişim"],
            "roles": tr_roles,
        },
        "en": {
            "current_role": "Data Analyst",
            "profile": {"seniority": "Mid-level"},
            "skill_names": ["SQL"],
            "radar_labels": ["Communication"],
            "roles": en_roles,
        },
    }


def test_user_preferred_locale_is_persisted_and_returned(client):
    auth = register_and_headers(client)

    assert client.get("/api/v1/auth/me", headers=auth).json()["preferred_locale"] == "tr"
    changed = client.patch(
        "/api/v1/auth/me/locale",
        headers=auth,
        json={"preferred_locale": "en"},
    )

    assert changed.status_code == 200
    assert changed.json()["preferred_locale"] == "en"
    assert client.get("/api/v1/auth/me", headers=auth).json()["preferred_locale"] == "en"
    assert client.patch(
        "/api/v1/auth/me/locale",
        headers=auth,
        json={"preferred_locale": "de"},
    ).status_code == 422


def test_new_analysis_stores_both_panel_languages_independent_of_cv_language(client, monkeypatch):
    register_and_headers(client)
    db = db_session()
    row = CareerAnalysis(
        id="localized-analysis",
        user_id=1,
        status="queued",
        source="upload",
        cv_text="Experienced data analyst with SQL and dashboard projects.",
    )
    db.add(row)
    db.commit()
    prompts = []

    def fake_invoke(prompt, schema):
        prompts.append(prompt)
        if schema is CareerAnalysisAI:
            return CareerAnalysisAI.model_validate(analysis_payload())
        if schema is CareerAnalysisLocalizationsAI:
            return CareerAnalysisLocalizationsAI.model_validate(analysis_localizations_payload())
        raise AssertionError(schema)

    monkeypatch.setattr(career_engine, "_invoke", fake_invoke)
    career_engine.analyze_row(db, row)

    assert row.status == "ready"
    assert set(row.localizations) == {"tr", "en"}
    assert career_engine.serialize_analysis(row, "tr")["career_ladder"][0]["title"] == "Veri Analisti"
    english = career_engine.serialize_analysis(row, "en")
    assert english["current_role"] == "Data Analyst"
    assert english["radar"][0] == {"label": "Communication", "score": 70, "target": 85}
    assert english["career_ladder"][0]["swot"]["strengths"] == ["Strong SQL"]
    assert "CV'nin dili çıktı dilini belirlemez" in prompts[0]
    assert "Python, SQL, AWS" in prompts[1]
    db.close()


def test_new_plan_localizes_task_text_but_preserves_external_course_title(client, monkeypatch):
    register_and_headers(client)
    db = db_session()
    analysis = CareerAnalysis(
        id="plan-analysis",
        user_id=1,
        status="ready",
        source="upload",
        cv_text="Data analyst",
        current_role="Veri Analisti",
        profile={},
        skills=[{"name": "SQL", "score": 80}],
        radar=[],
        career_ladder=[],
        localizations={
            "tr": {"current_role": "Veri Analisti", "profile": {}, "skills": [{"name": "SQL", "score": 80}], "radar": [], "career_ladder": []},
            "en": {"current_role": "Data Analyst", "profile": {}, "skills": [{"name": "SQL", "score": 80}], "radar": [], "career_ladder": []},
        },
    )
    target = CareerTarget(id="localized-target", user_id=1, title="Data Engineer", source="custom", status="queued")
    db.add_all([analysis, target])
    db.commit()

    def fake_invoke(_prompt, schema):
        if schema is CareerPlanAI:
            return CareerPlanAI.model_validate({
                "target_title": "Veri Mühendisi",
                "tasks": [{
                    "title": "Bir veri hattı geliştir",
                    "hint": "Python ve SQL kullan",
                    "evidence_required": True,
                    "evidence_types": ["link"],
                    "skill_impacts": ["Veri modelleme"],
                    "training_queries": [{"query": "data engineering course", "skill": "Data Engineering", "reason": "Beceri açığı"}],
                }],
            })
        if schema is CareerPlanLocalizationsAI:
            return CareerPlanLocalizationsAI.model_validate({
                "tr": {"target_title": "Veri Mühendisi", "tasks": [{"id": "0", "title": "Bir veri hattı geliştir", "hint": "Python ve SQL kullan", "skill_impacts": ["Veri modelleme"], "feedback": None}]},
                "en": {"target_title": "Data Engineer", "tasks": [{"id": "0", "title": "Build a data pipeline", "hint": "Use Python and SQL", "skill_impacts": ["Data modeling"], "feedback": None}]},
            })
        raise AssertionError(schema)

    monkeypatch.setattr(career_engine, "_invoke", fake_invoke)
    monkeypatch.setattr(career_engine, "search_training", lambda *_args: [{
        "catalog_id": "course-1",
        "title": "AWS Data Engineering Professional Certificate",
        "provider": "Coursera",
        "url": "https://example.com/course",
        "skills": ["AWS"],
    }])

    career_engine.plan_target(db, target)
    task = db.query(CareerTask).filter_by(target_id=target.id).one()

    assert career_engine.serialize_target(target, "en")["title"] == "Data Engineer"
    assert career_engine.serialize_target(target, "en")["plan"]["tasks"][0]["title"] == "Build a data pipeline"
    english = career_engine.serialize_task(task, db, "en")
    assert english["title"] == "Build a data pipeline"
    assert english["hint"] == "Use Python and SQL"
    assert english["training_suggestions"][0]["title"] == "AWS Data Engineering Professional Certificate"
    db.close()


def test_skill_evidence_task_is_created_in_both_panel_languages(client):
    register_and_headers(client)
    db = db_session()
    target = CareerTarget(id="skill-target", user_id=1, title="Data Engineer", source="custom", status="active")
    db.add(target)
    db.commit()

    task = career_engine.ensure_skill_evidence_task(db, 1, target.id, "AWS")

    assert career_engine.serialize_task(task, db, "tr")["title"] == "AWS kanıtı"
    assert career_engine.serialize_task(task, db, "en")["title"] == "AWS evidence"
    assert "GitHub" in career_engine.serialize_task(task, db, "en")["hint"]
    db.close()


def test_legacy_content_is_backfilled_once_and_cached_for_both_languages(client, monkeypatch):
    auth = register_and_headers(client)
    db = db_session()
    db.add(CareerAnalysis(
        id="legacy-analysis",
        user_id=1,
        status="ready",
        source="upload",
        cv_text="English CV",
        current_role="Data Analyst",
        profile={"seniority": "Mid-level"},
        skills=[{"name": "SQL", "score": 80}],
        radar=[{"label": "Communication", "score": 70, "target": 85}],
        career_ladder=analysis_payload()["roles"],
        localizations={},
    ))
    db.commit()
    calls = []

    def fake_invoke(_prompt, schema):
        calls.append(schema)
        return CareerAnalysisLocalizationsAI.model_validate(analysis_localizations_payload())

    monkeypatch.setattr(career_engine, "_invoke", fake_invoke)

    first = client.get("/api/v1/career/analysis/current", headers=auth)
    second = client.get("/api/v1/career/analysis/current", headers=auth)
    assert first.status_code == 200
    assert first.json()["current_role"] == "Veri Analisti"
    assert second.json()["current_role"] == "Veri Analisti"
    assert calls == [CareerAnalysisLocalizationsAI]

    client.patch("/api/v1/auth/me/locale", headers=auth, json={"preferred_locale": "en"})
    english = client.get("/api/v1/career/analysis/current", headers=auth)
    assert english.json()["current_role"] == "Data Analyst"
    assert calls == [CareerAnalysisLocalizationsAI]
    db.close()


def test_legacy_plan_is_backfilled_once_and_keeps_course_titles_original(client, monkeypatch):
    auth = register_and_headers(client)
    db = db_session()
    db.add(CareerAnalysis(
        id="legacy-plan-analysis",
        user_id=1,
        status="ready",
        source="upload",
        cv_text="English CV",
        current_role="Data Analyst",
        profile={},
        skills=[],
        radar=[],
        career_ladder=[],
        localizations={
            "tr": {"current_role": "Veri Analisti", "profile": {}, "skills": [], "radar": [], "career_ladder": []},
            "en": {"current_role": "Data Analyst", "profile": {}, "skills": [], "radar": [], "career_ladder": []},
        },
    ))
    target = CareerTarget(
        id="legacy-plan-target",
        user_id=1,
        title="Data Engineer",
        source="custom",
        status="active",
        plan={"tasks": [{"id": "legacy-plan-task", "title": "Build a data pipeline"}]},
        localizations={},
    )
    task = CareerTask(
        id="legacy-plan-task",
        user_id=1,
        target_id=target.id,
        title="Build a data pipeline",
        hint="Use Python and SQL",
        status="pending",
        evidence_types=["link"],
        skill_impacts=["Data modeling"],
        training_suggestions=[{
            "catalog_id": "course-1",
            "title": "AWS Data Engineering Professional Certificate",
            "provider": "Coursera",
            "url": "https://example.com/course",
        }],
        localizations={},
    )
    db.add_all([target, task])
    db.commit()
    calls = []

    def fake_invoke(_prompt, schema):
        calls.append(schema)
        return CareerPlanLocalizationsAI.model_validate({
            "tr": {"target_title": "Veri Mühendisi", "tasks": [{"id": task.id, "title": "Bir veri hattı geliştir", "hint": "Python ve SQL kullan", "skill_impacts": ["Veri modelleme"], "feedback": None}]},
            "en": {"target_title": "Data Engineer", "tasks": [{"id": task.id, "title": "Build a data pipeline", "hint": "Use Python and SQL", "skill_impacts": ["Data modeling"], "feedback": None}]},
        })

    monkeypatch.setattr(career_engine, "_invoke", fake_invoke)

    turkish = client.get(f"/api/v1/career/targets/{target.id}/tasks", headers=auth)
    assert turkish.status_code == 200
    assert turkish.json()[0]["title"] == "Bir veri hattı geliştir"
    assert turkish.json()[0]["training_suggestions"][0]["title"] == "AWS Data Engineering Professional Certificate"
    assert calls == [CareerPlanLocalizationsAI]

    assert client.patch(
        "/api/v1/auth/me/locale",
        headers=auth,
        json={"preferred_locale": "en"},
    ).status_code == 200
    english_target = client.get(f"/api/v1/career/targets/{target.id}", headers=auth).json()
    english_task = client.get(f"/api/v1/career/targets/{target.id}/tasks", headers=auth).json()[0]
    assert english_target["title"] == "Data Engineer"
    assert english_target["plan"]["tasks"][0]["title"] == "Build a data pipeline"
    assert english_task["title"] == "Build a data pipeline"
    assert calls == [CareerPlanLocalizationsAI]
    db.close()


def test_localization_failure_returns_explicit_error_instead_of_wrong_language(client, monkeypatch):
    auth = register_and_headers(client)
    db = db_session()
    db.add(CareerAnalysis(
        id="failed-localization",
        user_id=1,
        status="ready",
        source="upload",
        cv_text="English CV",
        current_role="Data Analyst",
        profile={},
        skills=[],
        radar=[],
        career_ladder=[],
        localizations={},
    ))
    db.commit()
    db.close()
    monkeypatch.setattr(career_engine, "_invoke", lambda *_args: (_ for _ in ()).throw(AIProviderError("translation down")))

    response = client.patch(
        "/api/v1/auth/me/locale",
        headers=auth,
        json={"preferred_locale": "en"},
    )

    assert response.status_code == 503
    assert response.json()["detail"]["code"] == "career_localization_failed"
    assert client.get("/api/v1/auth/me", headers=auth).json()["preferred_locale"] == "tr"
    assert client.get("/api/v1/career/analysis/current", headers=auth).status_code == 503
