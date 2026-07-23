import assert from 'node:assert/strict';
import { describe, it } from 'node:test';
import { cvBuilderImport } from '../../resources/js/panel-cv-builder-import.js';

describe('cvBuilderImport', () => {
    it('polls an automatic upload draft until it is ready without navigating', async () => {
        const requests = [];
        const state = cvBuilderImport(
            { builder_draft_status: 'queued' },
            { statusUrl: '/status', queueUrl: '/queue', openUrl: '/builder' },
            {
                wait: async () => {},
                request: async (url) => {
                    requests.push(url);
                    return { builder_draft_status: 'ready', builder_draft_error: null };
                },
            },
        );

        await state.poll();

        assert.deepEqual(requests, ['/status']);
        assert.equal(state.ready, true);
        assert.equal(state.openUrl, '/builder');
    });

    it('queues an old uploaded CV on demand, then polls its draft', async () => {
        const requests = [];
        const state = cvBuilderImport(
            { builder_draft_status: 'not_requested' },
            { statusUrl: '/status', queueUrl: '/queue' },
            {
                wait: async () => {},
                request: async (url, options = {}) => {
                    requests.push([url, options.method || 'GET']);
                    if (url === '/queue') return { builder_draft_status: 'queued' };
                    return { builder_draft_status: 'ready' };
                },
            },
        );

        await state.queue();

        assert.deepEqual(requests, [['/queue', 'POST'], ['/status', 'GET']]);
        assert.equal(state.ready, true);
        assert.equal(state.busy, false);
    });

    it('keeps a failed draft retryable and shows the safe API error', async () => {
        const state = cvBuilderImport(
            { builder_draft_status: 'failed', builder_draft_error: 'Alanlar hazırlanamadı.' },
            { statusUrl: '/status', queueUrl: '/queue' },
            {
                request: async () => {
                    throw new Error('Kuyruk geçici olarak kullanılamıyor.');
                },
            },
        );

        await state.queue();

        assert.equal(state.canQueue, true);
        assert.equal(state.error, 'Kuyruk geçici olarak kullanılamıyor.');
        assert.equal(state.busy, false);
    });

    it('hides the open action after the imported draft is persisted', () => {
        const unopened = cvBuilderImport({ builder_draft_status: 'ready', builder_opened: false });
        const opened = cvBuilderImport({ builder_draft_status: 'ready', builder_opened: true });

        assert.equal(unopened.canOpen, true);
        assert.equal(opened.canOpen, false);
        assert.equal(opened.opened, true);
    });
});
