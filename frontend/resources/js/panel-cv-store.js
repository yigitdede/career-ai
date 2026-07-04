export const PANEL_CV_STORAGE_KEY = 'panel-cv-state';

function formatAnalyzedAt(locale) {
    const d = new Date();
    if (locale === 'en') {
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    return d.toLocaleDateString('tr-TR', { day: 'numeric', month: 'short', year: 'numeric' });
}

/** Demo AI radar verisi — görsel şölen; gerçek analiz API'si bağlanınca güncellenecek. */
export function demoSkillAnalysis(locale) {
    const lang = locale === 'en' ? 'en' : 'tr';

    return {
        overall_match: 72,
        analyzed_at: formatAnalyzedAt(lang),
        target_role: lang === 'en' ? 'Junior Data Analyst' : 'Junior Veri Analisti',
        skills: lang === 'en'
            ? [
                { label: 'SQL', score: 85, target: 90 },
                { label: 'Python', score: 72, target: 85 },
                { label: 'Excel', score: 88, target: 80 },
                { label: 'Statistics', score: 68, target: 75 },
                { label: 'Visualization', score: 45, target: 70 },
                { label: 'Communication', score: 76, target: 80 },
                { label: 'English', score: 62, target: 75 },
                { label: 'Domain knowledge', score: 58, target: 65 },
            ]
            : [
                { label: 'SQL', score: 85, target: 90 },
                { label: 'Python', score: 72, target: 85 },
                { label: 'Excel', score: 88, target: 80 },
                { label: 'İstatistik', score: 68, target: 75 },
                { label: 'Görselleştirme', score: 45, target: 70 },
                { label: 'İletişim', score: 76, target: 80 },
                { label: 'İngilizce', score: 62, target: 75 },
                { label: 'Alan bilgisi', score: 58, target: 65 },
            ],
    };
}

function readState() {
    try {
        const raw = localStorage.getItem(PANEL_CV_STORAGE_KEY);
        return raw ? JSON.parse(raw) : null;
    } catch {
        return null;
    }
}

function writeState(state) {
    localStorage.setItem(PANEL_CV_STORAGE_KEY, JSON.stringify(state));
    window.dispatchEvent(new CustomEvent('panel-cv-updated', { detail: state }));
}

export const PanelCvStore = {
    get: readState,

    saveBuilder(locales, locale) {
        const persistedLocales = JSON.parse(JSON.stringify(locales));
        const rawName = (persistedLocales?.tr?.personal?.full_name || persistedLocales?.en?.personal?.full_name || 'cv').trim();
        const fileName = `${(rawName || 'cv').replace(/\s+/g, '-').toLowerCase()}-builder.json`;
        const state = {
            source: 'builder',
            fileName,
            locales: persistedLocales,
            savedAt: new Date().toISOString(),
        };
        writeState(state);

        return state;
    },

    saveUpload(fileName, locale) {
        const state = {
            source: 'upload',
            fileName,
            skillRadar: demoSkillAnalysis(locale),
            savedAt: new Date().toISOString(),
        };
        writeState(state);

        return state;
    },

    saveFromAnalysis(fileName, locale, skillRadar) {
        const state = {
            source: 'upload',
            fileName,
            skillRadar: {
                ...skillRadar,
                analyzed_at: skillRadar.analyzed_at ?? formatAnalyzedAt(locale === 'en' ? 'en' : 'tr'),
            },
            savedAt: new Date().toISOString(),
        };
        writeState(state);

        return state;
    },

    clear() {
        localStorage.removeItem(PANEL_CV_STORAGE_KEY);
        window.dispatchEvent(new CustomEvent('panel-cv-updated', { detail: null }));
    },
};

export function panelCvRadar(labels, serverHasCv = false, serverFileName = '', clearUrl = '') {
    return {
        labels,
        hasCv: Boolean(serverHasCv),
        cvFileName: serverFileName || '',
        clearUrl,

        init() {
            if (serverHasCv) {
                this.hasCv = true;
                this.cvFileName = serverFileName || this.cvFileName;
                return;
            }

            const state = PanelCvStore.get();
            this.hasCv = Boolean(state?.skillRadar);
            this.cvFileName = state?.fileName ?? '';
            window.addEventListener('panel-cv-updated', () => this.refresh());
        },

        refresh() {
            const state = PanelCvStore.get();
            this.hasCv = Boolean(state?.skillRadar);
            this.cvFileName = state?.fileName ?? '';
        },

        cvFileDisplay() {
            return (this.labels.cv_file || '').replace(':name', this.cvFileName || 'cv');
        },

        async clearCv() {
            if (this.clearUrl) {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                try {
                    await fetch(this.clearUrl, {
                        method: 'POST',
                        headers: token ? { 'X-CSRF-TOKEN': token, Accept: 'application/json' } : { Accept: 'application/json' },
                    });
                } catch {
                    // session clear best-effort
                }
            }

            PanelCvStore.clear();
            window.location.reload();
        },
    };
}

export function profileCvUpload(locale, analyzeUrl) {
    return {
        fileName: null,
        locale,
        analyzeUrl,
        loading: false,
        error: null,

        init() {
            const state = PanelCvStore.get();
            if (state?.fileName) {
                this.fileName = state.fileName;
            }
        },

        async onFileSelect(event) {
            const file = event.target.files?.[0];
            if (!file) {
                return;
            }

            this.fileName = file.name;
            this.error = null;
            this.loading = true;

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const body = new FormData();
                body.append('cv', file);

                const response = await fetch(this.analyzeUrl, {
                    method: 'POST',
                    headers: token ? { 'X-CSRF-TOKEN': token, Accept: 'application/json' } : { Accept: 'application/json' },
                    body,
                });

                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(payload.message || 'CV analizi başarısız');
                }

                const radar = payload.skill_radar ?? demoSkillAnalysis(this.locale);
                PanelCvStore.saveFromAnalysis(file.name, this.locale, radar);

                if (payload.redirect) {
                    window.location.href = payload.redirect;
                    return;
                }
            } catch (err) {
                this.error = err?.message || 'CV analizi başarısız';
                this.fileName = null;
            } finally {
                this.loading = false;
                event.target.value = '';
            }
        },

        removeCv() {
            this.fileName = null;
            this.error = null;
            PanelCvStore.clear();
        },
    };
}
