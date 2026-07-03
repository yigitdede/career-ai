# CareerTalent AI 🚀

**Grup 92 — YZTA Bootcamp 2026 Projesi**

Repo: https://github.com/busebatan/careertalent-ai

**Plan A:** FastAPI backend + Laravel frontend

> **Mimari not:** Sprint 1–2 **Plan A** ile başlanır. Gerekirse Sprint 1 veya 2 sonrası **Plan B** (Laravel ana + Python worker) geçişi değerlendirilir. Detay: [Teknik Mimari](docs/teknik-mimari.md#mimari-karar-ve-geçiş-planı).

---

## Problem

YZTA bootcamp ve benzeri programlardan mezun olan veya mezun olmaya hazırlanan öğrenciler:

1. **CV'sini** objektif okuyup hangi mesleklere uygun olduğunu **sıralı** göremiyor.
2. ChatGPT'ye sorunca **tek seferlik metin** alıyor; haftalarca **ilerleme takibi** yok.
3. "Şimdi yapabilirim" ile "eksikleri kapatınca ulaşırım" meslekler **karışıyor**.
4. SWOT genelde **soyut**; hangi madde CV'den, hangisi pazardan geliyor belli değil.
5. Eksik yetenek için **hangi ücretsiz/ücretli kaynağa** gideceği ve **ne kadar süre** gerektiği net değil.
6. Mentör / cohort hangi öğrencinin **takıldığını** tek ekranda göremiyor.

---

## Çözüm

CareerTalent AI, adayın PDF CV'sini analiz ederek **ölçülebilir hazırlık skoru**, **kariyer merdiveni** ve **haftalık yol haritası** üretir. Kendi kurs sunmaz; eksik yetenekler için harici eğitim ve sertifika kaynaklarına yönlendirir.

### Akış

```
CV yükle (PDF)
    → AI parse + profil çıkarımı
    → Meslek skoru (readiness %) her rol için
    → Kariyer merdiveni (A → B → C)
    → Rol başına SWOT (kanıtlı)
    → Gap → haftalık yol haritası → eğitim/sertifika önerisi (harici link)
    → (Faz 2) Gerçek iş ilanı eşleştirmesi
```

### Kariyer merdiveni

| Kademe | Ad | Kriter | Anlam |
|--------|-----|--------|-------|
| **A** | Hazır | readiness ≥ %70 | Şimdi başvuruya yakın |
| **B** | Yakın | %40–69 | 4–8 haftalık planla ulaşılabilir |
| **C** | Ulaşılabilir | <%40 | Uzun vade; eksikler tamamlanınca mümkün |

Liste önce A, sonra B, sonra C kademelerine göre sıralanır.

> ChatGPT kariyer koçu verir; CareerTalent **ölçülebilir hazırlık, kariyer merdiveni, haftalık plan ve pazar gerçekliği** verir.

Detaylı ürün tanımı: [Ürün Değeri v001](docs/urun/2026-06-29-v001-urun-degeri.md)

---

## Temel Özellikler

| Özellik | Durum |
|---------|-------|
| Akıllı CV ayrıştırma (pdfplumber + Gemini) | Sprint 1 |
| Tanıtım sitesi + öğrenci paneli | Sprint 1 |
| Kariyer merdiveni ve readiness skoru | Sprint 2 |
| SWOT (kanıtlı, CV'den) | Sprint 2 |
| Haftalık yol haritası | Sprint 2 |
| Harici eğitim / sertifika önerisi | Sprint 2–3 |
| İş ilanı semantic eşleştirme | Faz 2 |
| Kariyer sohbet ajanı (LangChain) | Sprint 3 (planlı) |

---

## Teknoloji Yığını

| Katman | Klasör | Teknoloji |
|--------|--------|-----------|
| **Frontend (UI)** | `frontend/` | Laravel 13, Blade, Livewire, Tailwind |
| **Backend (API)** | `backend/` | FastAPI, SQLAlchemy, Celery |
| **Yapay zeka** | `backend/` | LangChain, Gemini API |
| **ML / benzerlik** | `backend/` | NumPy, Scikit-learn (cosine similarity) |
| **Veritabanı** | `backend/` | PostgreSQL (+ Redis kuyruk) |
| **Oturum (UI)** | `frontend/` | SQLite (yalnızca session/cache) |

---

## Mimari

| Katman | Port |
|--------|------|
| FastAPI API | `:8000` |
| Laravel web | `:8080` |

Laravel tanıtım sitesi ve öğrenci/mentör panelini sunar. İş mantığı, veritabanı ve yapay zeka **FastAPI** tarafındadır.

---

## Kurulum (lokal)

### 0. Repoyu klonlayın

```bash
git clone https://github.com/busebatan/careertalent-ai.git
cd careertalent-ai
```

### 1. Backend (FastAPI)

```bash
cd backend
python -m venv .venv && source .venv/bin/activate
pip install -r requirements.txt
cp .env.example .env
# backend/.env içine GEMINI_API_KEY=... ekleyin
uvicorn app.main:app --reload --port 8000
```

Sağlık kontrolü: http://localhost:8000/health

### 2. Frontend (Laravel)

```bash
cd frontend
cp .env.example .env
composer install
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm install && npm run build
php artisan serve --port=8080
```

- Tanıtım: http://localhost:8080
- Panel: http://localhost:8080/panel

`frontend/.env` içinde `CAREERTALENT_API_URL=http://localhost:8000` olmalı.

### Docker (opsiyonel)

```bash
cp .env.example .env
docker compose up
```

- API: http://localhost:8000
- Web: http://localhost:8080

---

## Sprint 1 Durumu

| Tarih | 19 Haziran – 5 Temmuz 2026 |
|-------|----------------------------|
| Hedef | Kayıt/giriş, CV yükleme başlangıcı, tanıtım sitesi, API sözleşmesi v0 |

| Alan | Durum | Not |
|------|-------|-----|
| Tanıtım sitesi (`/`, `/ozellikler`) | Tamamlandı | Marketing sayfaları ve testler mevcut |
| Panel iskeleti (`/panel/*`) | Kısmen | UI rotaları hazır; demo veri ile çalışıyor |
| FastAPI health | Tamamlandı | `GET /health`, `GET /health/ready` |
| Auth + CV upload API | Devam ediyor | Sprint 1 hedefi |
| Otomatik testler | Kısmen | Backend + frontend test dosyaları mevcut |

Detay plan ve sprint raporu: [Sprint 1](docs/sprintler/sprint-1-ilk-sprint.md) · [Bootcamp takvimi](docs/bootcamp-takvimi.md)

---

## Dokümantasyon

- [Teknik Mimari](docs/teknik-mimari.md)
- [Bootcamp Takvimi ve Sprint Raporları](docs/bootcamp-takvimi.md)
- [Ürün Değeri ve Fark (sürümlü)](docs/urun/README.md)

---

## Eski Stack

İlk Streamlit MVP ve eski Laravel monolith denemesi `archive/` klasöründe referans olarak duruyor.

---

## Takım

| İsim | Rol | Odak |
|------|-----|------|
| Buse Batan | Scrum Master + Frontend + Teknik Mimari | Laravel arayüz, mimari, sprint yönetimi |
| Bithanya Abraham Haile | Frontend + Sunum | UI/UX, tasarım, bootcamp demosu |
| Döne Sakız | Backend | FastAPI, API, servisler, veritabanı |
| Yiğit Dede | Veri & Analiz + Product Owner | CV parse, scraper, embedding, eşleştirme verisi |
