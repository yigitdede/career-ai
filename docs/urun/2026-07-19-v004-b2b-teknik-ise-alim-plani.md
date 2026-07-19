# CareerTalent AI — B2B Teknik İşe Alım İş Planı v004

| | |
|---|---|
| **Sürüm** | v004 |
| **Tarih** | 2026-07-19 |
| **Önceki sürüm** | [v003 — Jüri, readiness ve YZTA paketi](2026-06-29-v003-juri-readiness-yzta-paket.md) |
| **Durum** | Uygulama başlangıç sözleşmesi |
| **Karar ufku** | 90 günlük ücretli pilot |

## Önceki sürüme göre değişiklik

| Alan | v003 | v004 |
|---|---|---|
| Birincil müşteri | Bootcamp/YZTA cohort | Teknik işe alım yapan KOBİ, kurumsal İK ve ajans |
| Ana değer | Aday readiness ve cohort görünürlüğü | İlan–aday açıklanabilir uyum ve insan kontrollü kısa liste |
| Gelir | Cohort lisansı + B2C devam | İşveren aboneliği + tamamlanan değerlendirme kullanımı |
| Panel sınırı | Öğrenci ve platform admin | Aday paneli + ayrı kurum tenant paneli + platform admin |
| İlk teslim | Mentör/cohort planı | Tenant çekirdeği ve `/admin/kurumlar` |

## 1. Yönetici kararı

CareerTalent AI, ilk aşamada LinkedIn/Kariyer.net benzeri genel bir ilan sitesi olmayacak. Ürün, ilan ile insan mülakatı arasına giren **açıklanabilir teknik uygunluk ve değerlendirme katmanı** olacak.

Tek cümlelik konumlandırma:

> CareerTalent, teknik rollerde başvuran adayın ilanla uyumunu kanıtlarıyla açıklar; sıralamayı hızlandırır, eleme kararını insanda bırakır.

Kararlar:

- Ana gelir B2B işveren/ajans aboneliği ve tamamlanan değerlendirme kullanımıdır.
- Aday, işe başvuru ve işveren değerlendirmesi için ücret ödemez.
- KOBİ, kurumsal İK ve işe alım ajansı aynı tenant çekirdeğini kullanır; paket ve yetkiler ayrılır.
- İlk kanal CareerTalent üzerinde barındırılan ilan + başvuru akışıdır.
- Sistem otomatik eleme yapmaz. İK, açıklamalı uyum skoru ve kanıt kartına göre karar verir.
- `/admin` platform operasyonudur; aday `/panel`, işveren/ajans ise ayrı kurum çalışma alanı kullanır.
- Mevcut aday `JobOpportunity`, `JobApplication` ve `CareerInterview` kayıtları işe alım tablolarına dönüştürülmez.

## 2. Paylaşılan görsellerin değerlendirmesi

### 2.1 Korunacak fikirler

| Fikir | Karar | Neden |
|---|---|---|
| Teknik yetkinlik skoru | Korunur | CareerTalent'ın mevcut readiness, skill ve kanıt motoruna dayanır. |
| Kör/anonim ilk inceleme | Korunur | İsim/fotoğraf yerine iş ilişkili kanıtı öne çıkarır. |
| Job-to-candidate eşleştirme | Korunur | İlan gereksinimi ile aday yetkinliğini aynı sözlükte karşılaştırır. |
| B2B ödeyen, aday ücretsiz | Korunur | Değeri alan ve bütçesi olan taraf işverendir. |
| ATS bağlantısı | Sonraki faz | Ürün doğrulandıktan sonra API/widget/connector olarak eklenir. |

### 2.2 Değiştirilecek fikirler

| Görseldeki öneri | Yeni sözleşme | Gerekçe |
|---|---|---|
| Her aday mülakatı bitirmeden CV görünmesin | Başvuru hızlı alınır; gerekli kısa değerlendirme tamamlanınca skor kartı zenginleşir | Zorunlu uzun mülakat başvuru tamamlama oranını düşürebilir. |
| Skor 80 üstünü sistem seçsin | Sistem açıklar ve sıralar; insan kısa liste/ret kararı verir | KVKK itirazı, adalet, güven ve yanlış negatif riski. |
| Baştan RAG/vector veritabanı | Önce normalize skill + ağırlıklı kural + değerlendirme; semantik rerank ölçüm sonrası | RAG ürün hedefi değil, olası uygulama aracıdır. Verisiz karmaşıklık moat oluşturmaz. |
| İlk ürün embed widget olsun | İlk ürün hosted ilan ve başvuru | Uçtan uca funnel ölçümü, düşük kurulum maliyeti, daha hızlı pilot. |
| Genel ilan sitesi | Teknik işe alım katmanı | İki taraflı pazar likiditesi oluşmadan genel ilan sitesine dönüşmek dağıtım problemine çarpar. |

## 3. Hedef pazar ve roller

Şirket büyüklüğü yerine ortak iş problemi hedeflenir:

> Junior–mid yazılım, veri ve AI rollerinde ilan başına en az 20 başvuru alan; teknik ön elemede zaman, standardizasyon veya kanıt problemi yaşayan ekipler.

| Segment | İlk değer | Sonraki paket farkı | Satın alan |
|---|---|---|---|
| KOBİ / startup | 1 günde ilan, standart skor kartı, kısa liste | Basit takım yetkisi, sınırlı aktif ilan | Kurucu, HR lead |
| Kurumsal İK | Tutarlı değerlendirme, audit, rapor | SSO, SLA, veri saklama politikası, gelişmiş RBAC | İK direktörü, satın alma |
| İşe alım ajansı | Çoklu müşteri ve yüksek aday hacmi | Müşteri workspace, white-label, hacim kredisi | Ajans sahibi, operasyon lideri |

Kullanıcı rolleri:

- **Ekonomik alıcı:** HR direktörü, kurucu veya ajans sahibi.
- **Günlük kullanıcı:** recruiter / talent acquisition uzmanı.
- **Karar ortağı:** hiring manager / teknik ekip lideri.
- **Veri sahibi:** aday.
- **Platform operatörü:** CareerTalent admin.

## 4. Ürün yüzeyleri ve veri ilişkisi

| Yüzey | Amaç | Görebildiği veri | Görememesi gereken veri |
|---|---|---|---|
| `/panel` aday | Kariyer profili, başvurular, değerlendirmeler, izinler | Kendi CV, kanıt, skor ve başvuruları | Diğer adaylar, işveren iç notları |
| Kurum paneli | İlan, pipeline, kör skor kartı, ekip ve kullanım | Yalnız kendi tenant ilan/başvuruları | Başka kurumlar; başvuru/izin dışı aday profili |
| `/admin` platform | Tenant, plan, kullanım, model sürümü, itiraz ve operasyon | Platform yönetimi için gerekli kayıtlar | Varsayılan olarak recruiter gibi aday eleme yetkisi |

Temel veri sözleşmesi:

1. Adayın kariyer profili adaya aittir.
2. Aday bir ilana başvurunca işverene **başvuru anı skor/kanıt snapshot'ı** açılır.
3. İlk incelemede isim, fotoğraf, yaş, cinsiyet ve benzeri işe ilgisiz sinyaller skor dışında ve gizli kalır.
4. İK kısa liste/ret/kimlik açma kararını kendisi verir; karar ve gerekçe audit kaydına girer.
5. Aday başvuru, veri kullanımı, saklama süresi ve kimlik açma durumunu görür.

### 4.1 Yeni domain tabloları

Mevcut aday tablolarına dokunmadan yeni işe alım domain'i:

- `organizations`
- `organization_memberships`
- `hiring_jobs`
- `hiring_job_requirements`
- `hiring_applications`
- `hiring_assessment_templates`
- `hiring_assessment_attempts`
- `hiring_scorecards`
- `hiring_decisions`
- `candidate_consents`
- `audit_events`
- `subscriptions`
- `usage_ledger`

Her işveren kaydı `organization_id` ile tenant-scope edilir. Kullanıcıya tek global `employer` rolü vermek yerine üyelik tablosu kullanılır; aynı kişi birden fazla kuruma farklı rolle üye olabilir.

## 5. Ana kullanıcı akışı

### 5.1 İşveren

1. Kurum oluşturulur, owner/recruiter davet edilir.
2. İlan başlığı, görevler, must-have ve preferred skill'ler tanımlanır.
3. Sistem ilan metnini normalize eder; İK ağırlıkları onaylar.
4. Hosted ilan yayınlanır.
5. Başvurular pipeline'a düşer.
6. Uygunluk kartı: zorunlu skill, tercih edilen skill, kanıt, değerlendirme ve açıklama.
7. İK filtreler; kısa liste/ret/kimliği aç kararını verir.
8. Funnel ve kalite raporu oluşur.

### 5.2 Aday

1. İlanı görür; hesapla veya düşük sürtünmeli başvuruyla ilerler.
2. CV/profile verisi için amaç ve saklama bilgilendirmesini görür.
3. Başvurur; ilanın gerektirdiği kısa değerlendirmeyi tamamlar.
4. Başvuru durumu ve kullanılan skor bileşenlerini görür.
5. Yanlış veri düzeltme ve insan incelemesi talep edebilir.

## 6. Eşleştirme motoru sözleşmesi

İlk sürüm hibrit ve açıklanabilir olur:

1. **Uygunluk sinyalleri:** lokasyon/çalışma izni/dil gibi ilan tarafından açıkça tanımlanan koşullar. Otomatik ret yok; bayrak üretir.
2. **Yapılandırılmış skill match:** normalize zorunlu ve tercih edilen yetkinlikler.
3. **İş örneği/değerlendirme:** role özgü kısa ve işle ilişkili görev.
4. **Kanıt güveni:** proje, GitHub, sertifika veya CV deneyimi.
5. **Semantik rerank:** yalnız offline değerlendirme, ölçülebilir kazanım gösterirse eklenir.

Başlangıç hipotezi:

```text
uyum = %45 zorunlu skill + %20 tercih edilen skill
     + %25 değerlendirme + %10 kanıt güveni
```

Bu ağırlık ürün gerçeği değildir; `score_version` ile sürümlenir ve pilot verisiyle kalibre edilir. Her skor şu soruyu yanıtlamalıdır: “Neden bu puan?”

Değerlendirme kapısı:

- En az 10 ilan × 10 aday için recruiter ikili değerlendirmesi.
- Top-10 precision, recruiter sıralama uyumu ve mülakata geçiş oranı ölçülür.
- Demografik alanlar skor girdisi olamaz.
- Bir aday otomatik reddedilemez.

## 7. Gelir modeli

### 7.1 Ana model

**Kurum aboneliği + tamamlanan değerlendirme kullanımı.** Bu model hem ATS aboneliği hem de değerlendirme kredisi pazar davranışıyla uyumludur: Workable şirket planı ve AI kredisi; TestGorilla aday/değerlendirme kredisi kullanır.

| Paket | Fiyat hipotezi | Dahil | Amaç |
|---|---:|---|---|
| 90 günlük ücretli pilot | 750–1.500 USD karşılığı + KDV | 3 aktif ilan, 3 koltuk, kurulum, haftalık kalibrasyon | Değer ve ödeme doğrulama |
| Starter | 99 USD/ay karşılığı | 3 aktif ilan, 2 recruiter, temel rapor | KOBİ |
| Growth | 249 USD/ay karşılığı | 10 aktif ilan, 5 recruiter, gelişmiş rapor/API export | Büyüyen ekip |
| Agency | 399 USD/ay karşılığı | Çoklu müşteri çekirdeği, hacim kredisi | Ajans |
| Enterprise | Teklif | SSO, SLA, audit/export, özel retention | Kurumsal |

Fiyatlar taahhüt değil, satış görüşmesinde test edilecek hipotezdir. Türkiye sözleşmesinde fatura günündeki TL karşılığı kullanılabilir.

Ek gelir:

- Paket üstü tamamlanan değerlendirme kredisi.
- Enterprise kurulum/veri aktarımı.
- Doğrulanmış ATS connector paketi.
- Ajans white-label ve müşteri workspace'i.

İlk 90 günde yapılmayacak gelirler:

- Adaydan başvuru veya işe yerleşme ücreti.
- Hukuki kapsam netleşmeden başarı/yerleştirme komisyonu.
- Skoru yüksek göstermek için adaydan ücret.
- Üçüncü taraf eğitim önerisini gizli reklamla sıralamak.

### 7.2 Birim ekonomi kapıları

```text
Net katkı = abonelik + kullanım geliri
           - AI/parse maliyeti - altyapı - ödeme maliyeti - değişken destek
```

Pilot çıkış eşiği:

- Brüt marj hedefi ≥ %75.
- AI + altyapı maliyeti ≤ net gelirin %15'i.
- Kurum başına aylık manuel operasyon ≤ 2 saat.
- Ücretli pilotların en az %50'si devam/abonelik görüşmesine geçer.

Örnek yıllık senaryo, sadece kapasite testi: 10 Starter + 5 Growth + 2 Agency = 36.396 USD-equivalent ARR; Enterprise ve kullanım aşımı hariç.

## 8. Go-to-market

Tek ürün, üç mesaj:

- **KOBİ:** “100 CV okumadan, neden uygun olduğunu gör.”
- **Kurumsal:** “Her ekipte aynı iş ilişkili skor kartı ve audit izi.”
- **Ajans:** “Birden çok müşteri için teknik kısa listeyi standardize et.”

İlk satış kanalları:

1. Kurucu liderliğinde doğrudan satış; teknik rol ilanı açık şirketler.
2. YZTA/bootcamp ağı üzerinden işe alım yapan partner şirketler.
3. HR ve startup topluluklarında kapalı demo.
4. Bir KOBİ, bir kurumsal ekip, bir ajans ile problem görüşmesi; ilk çalışan pilot tek tenant çekirdeğiyle.

Satış görüşmesi kanıtı:

- Son teknik ilanda başvuru sayısı.
- CV ön eleme için harcanan saat.
- Kaç aday teknik görüşmede bariz uyumsuz çıktı.
- Standardizasyon ve audit ihtiyacı.
- Ödemeye değer bulunan sonuç: zaman, kısa liste kalitesi veya aday deneyimi.

## 9. 90 günlük uygulama planı

### Gün 1–14 — Problem ve yönetişim

- 12 alıcı görüşmesi: 4 KOBİ, 4 kurumsal, 4 ajans.
- 2 yazılı design-partner/pilot niyeti.
- İlk üç rol: Junior Backend, Data Analyst, AI/ML Junior.
- Skor kartı, izin metni, veri saklama ve insan kararı sözleşmesi.
- Başlangıç golden set'i.

**Çıkış:** En az iki müşteri aynı problem ve ölçülebilir başarı metriğini onaylar.

### Gün 15–35 — Tenant ve ilan temeli

- `organizations` + üyelik/RBAC.
- `/admin/kurumlar`: oluşturma, plan, durum, segment.
- Kurum paneli auth kabuğu.
- İlan CRUD + gereksinim ağırlıkları.
- Hosted ilan sayfası.

**Çıkış:** Admin kurum açar; recruiter yalnız kendi tenant'ında ilan yayınlar.

### Gün 36–60 — Başvuru ve skor kartı

- Aday izin/bilgilendirme.
- Başvuru ve application snapshot.
- Kısa role özgü değerlendirme.
- Açıklanabilir uyum bileşenleri.
- Kör kart ve kimlik açma olayı.

**Çıkış:** Bir aday hosted ilana başvurur; İK kör skor kartını görür, sistem eleme yapmaz.

### Gün 61–75 — Pipeline ve ölçüm

- New/review/shortlist/interview/rejected/hired aşamaları.
- İnsan karar nedeni ve audit.
- Funnel, süre ve değerlendirme tamamlama raporu.
- CSV export; ATS connector yok.

**Çıkış:** Uçtan uca hiring akışı ve KPI ölçümü.

### Gün 76–90 — Ücretli pilot

- Gerçek ilan ve gerçek başvurular.
- Haftalık skor kalibrasyonu.
- Aday destek/itiraz akışı.
- Maliyet, güvenilirlik, NPS ve recruiter uyumu.
- Devam teklifi ve 6 aylık backlog.

**Çıkış:** Ücret, kullanım, sonuç ve yenileme kanıtı.

## 10. KPI sistemi

**North Star:** Aktif ilan başına, İK tarafından kısa listeye alınan açıklanabilir uygun aday sayısı.

| Alan | Metrik | 90 günlük hedef hipotezi |
|---|---|---:|
| Hız | İlan → ilk nitelikli kısa liste medyanı | < 72 saat |
| Verim | İK'nın aday başına inceleme süresi | ≥ %40 azalma |
| Aday | Başvuru tamamlama | ≥ %65 |
| Aday | Değerlendirme tamamlama | ≥ %70 |
| Kalite | Kısa liste → teknik görüşme | Baz çizgiye göre artış |
| Güven | Skor açıklamasını yararlı bulan recruiter | ≥ %70 |
| Gelir | Pilot → ücretli devam görüşmesi | ≥ %50 |
| Sistem | Başarılı analiz/scorecard | ≥ %98 |

Guardrail:

- Otomatik ret: 0.
- Kimlik/PII sızıntısı: 0.
- İtiraz/insan incelemesi kanalı olmayan karar: 0.
- Skor girdisinde işe ilgisiz demografik alan: 0.

## 11. Admin panel kapsamı

### P0 — İlk dikey dilim

- Kurum listele/oluştur/güncelle.
- Kurum türü: employer/agency.
- Segment: SMB/mid-market/enterprise.
- Plan: pilot/starter/growth/agency/enterprise.
- Durum: onboarding/active/suspended/closed.
- `organizations.manage` admin izni.
- Hard delete yok; kapatma/suspend var.
- Çoklu kurum üyeliği için membership tablosu.

### P1

- Kurum detay: üyeler, aktif ilan, başvuru ve kullanım.
- Plan/credit ledger.
- AI hata oranı ve maliyet.
- Aday itiraz/silme talepleri.
- Audit event araması.

### P2

- Abonelik/fatura sağlayıcı entegrasyonu.
- Enterprise policy, retention ve export.
- Ajans müşteri workspace yönetimi.
- Feature flag ve model rollout.

## 12. Risk ve hukuk kapıları

- KVKK Madde 11, yalnız otomatik analizle aleyhe sonuç doğmasına itiraz hakkı verir; ürün bu nedenle insan kararını, açıklamayı ve itiraz akışını temel sözleşme yapar.
- AB pazarında CV filtreleme ve aday değerlendirme AI Act kapsamında yüksek riskli kullanım olabilir; EU açılımından önce risk yönetimi, veri yönetişimi, log, insan gözetimi ve dokümantasyon paketi gerekir.
- Türkiye'de iş ve işçi bulmaya aracılık, özel istihdam bürosu izni ve ilan yükümlülükleriyle kesişebilir. Public marketplace veya başarı komisyonu açılmadan önce İŞKUR kapsamı hukuk görüşüyle netleştirilir.
- Adaydan işe yerleştirme/başvuru ücreti alınmaz.
- Kamera tabanlı duygu, yüz veya kişilik çıkarımı yapılmaz.

Güncel birincil kaynaklar:

- [KVKK — İlgili kişinin hakları](https://www.kvkk.gov.tr/Icerik/2036/Ilgili-Kisinin-Haklari)
- [AB Komisyonu — AI Act risk sınıfları](https://digital-strategy.ec.europa.eu/en/policies/regulatory-framework-ai)
- [İŞKUR — Özel İstihdam Büroları Yönetmeliği](https://media.iskur.gov.tr/46970/ozel-istihdam-burolari-yonetmeligi.pdf)
- [Workable — şirket planı ve AI kredileri](https://www.workable.com/pricing)
- [TestGorilla — aday/değerlendirme kredileri](https://www.testgorilla.com/pricing/)

## 13. Yapılmayacaklar

- İlk 90 günde genel iş ilanı marketplace'i.
- LinkedIn/Kariyer.net scraping'e dayalı arz.
- Tam ATS, bordro veya onboarding ürünü.
- Otomatik aday reddi.
- Skoru tek LLM cevabına bırakmak.
- Ayrı vector database'i doğrulama olmadan eklemek.
- Aday kişisel tracker tablolarını işveren pipeline'ı için yeniden kullanmak.

## 14. İlk uygulama kabul kriteri

İlk teslim tamam sayılırsa:

1. Admin, gerçek backend API üzerinden kurum oluşturur, listeler ve günceller.
2. Yetkisiz admin `organizations.manage` olmadan erişemez.
3. Kurum slug'ı benzersizdir.
4. Tenant üyeliği veri modeli hazırdır; bir kullanıcı birden fazla kuruma üye olabilir.
5. Kurum hard-delete endpoint'i yoktur.
6. Backend contract testleri, Laravel feature testleri, Alembic upgrade ve production build geçer.
