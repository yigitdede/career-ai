import assert from 'node:assert/strict';
import { describe, it } from 'node:test';
import { panelJobListings } from '../../resources/js/panel-job-listings.js';

const jobs = [
    {
        organization: { name: 'ACME Teknoloji' },
        position: { title: 'Backend Developer', workplace_type: 'remote', employment_type: 'contract', location: 'Ankara', public_path: '/apply/acme/backend-ABC' },
    },
];

describe('panelJobListings', () => {
    it('filters by search, workplace and employment type together', () => {
        const state = panelJobListings(jobs, {});
        state.query = 'acme';
        state.workplace = 'remote';
        state.employment = 'contract';

        assert.deepEqual(state.filteredItems.map((item) => item.position.title), ['Backend Developer']);
    });

    it('opens the real application modal and submits through the API', async () => {
        const state = panelJobListings(jobs, {}, [{ id: 'cv-1', display_name: 'Ana CV.pdf' }]);
        state.applyUrl = '/panel/basvurularim';
        globalThis.document = { querySelector: () => null };
        globalThis.fetch = async () => ({ ok: true, json: async () => ({ id: 'application-1' }) });

        state.beginApplication(jobs[0]);
        assert.equal(state.applicationOpen, true);
        assert.equal(state.applicationJob, jobs[0]);
        state.selectedCvId = 'cv-1';
        state.applicationConsent = true;
        await state.completeApplication();

        assert.equal(state.applicationSubmitted, true);
        state.closeApplication();
        assert.equal(state.applicationOpen, false);
    });
});
