const DEFAULT_MAX_POLLS = 180;

async function defaultRequest(url, options = {}) {
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    const response = await fetch(url, {
        ...options,
        headers: {
            ...(token ? { 'X-CSRF-TOKEN': token } : {}),
            Accept: 'application/json',
            'Content-Type': 'application/json',
            ...(options.headers || {}),
        },
        ...(options.method === 'POST' ? { body: JSON.stringify({}) } : {}),
    });
    const payload = await response.json().catch(() => ({}));
    if (!response.ok) throw new Error(payload.message || 'CV alanları hazırlanamadı.');
    return payload;
}

export function cvBuilderImport(initialDocument = {}, config = {}, runtime = {}) {
    return {
        status: initialDocument?.builder_draft_status || 'not_requested',
        error: initialDocument?.builder_draft_error || '',
        opened: Boolean(initialDocument?.builder_opened),
        busy: false,
        polling: false,
        statusUrl: config.statusUrl || '',
        queueUrl: config.queueUrl || '',
        openUrl: config.openUrl || '',
        labels: config.labels || {},
        request: runtime.request || defaultRequest,
        wait: runtime.wait || ((milliseconds) => new Promise((resolve) => setTimeout(resolve, milliseconds))),
        maxPolls: runtime.maxPolls || DEFAULT_MAX_POLLS,

        get pending() {
            return this.status === 'queued' || this.status === 'running';
        },

        get ready() {
            return this.status === 'ready';
        },

        get canOpen() {
            return this.ready && !this.opened;
        },

        get canQueue() {
            return this.status === 'not_requested' || this.status === 'failed';
        },

        init() {
            if (this.pending) void this.poll();
        },

        apply(payload = {}) {
            this.status = payload.builder_draft_status || this.status;
            this.error = payload.builder_draft_error || '';
            this.opened = payload.builder_opened ?? this.opened;
        },

        async queue() {
            if (!this.canQueue || this.busy || !this.queueUrl) return;
            this.busy = true;
            this.error = '';
            try {
                this.apply(await this.request(this.queueUrl, { method: 'POST' }));
                if (this.pending) await this.poll();
            } catch (error) {
                this.error = error?.message || this.labels.failed || 'CV alanları hazırlanamadı.';
            } finally {
                this.busy = false;
            }
        },

        async poll() {
            if (this.polling || !this.statusUrl) return;
            this.polling = true;
            try {
                for (let attempt = 0; attempt < this.maxPolls && this.pending; attempt += 1) {
                    await this.wait(attempt === 0 ? 0 : 1000);
                    this.apply(await this.request(this.statusUrl));
                }
                if (this.pending) this.error = this.labels.timeout || 'CV alanları hâlâ hazırlanıyor. Sayfayı yenileyerek kontrol et.';
            } catch (error) {
                this.error = error?.message || this.labels.failed || 'CV alanları hazırlanamadı.';
            } finally {
                this.polling = false;
            }
        },
    };
}
