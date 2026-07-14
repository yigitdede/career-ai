import assert from 'node:assert/strict';
import { describe, it } from 'node:test';

globalThis.document = { querySelector: () => ({ getAttribute: () => 'token' }) };

const { skillPassport } = await import('../../resources/js/panel-skill-passport.js');

describe('skillPassport evidence contract', () => {
    it('selects a skill and opens upload for radar items without task_id', () => {
        const state = skillPassport(
            [{ skill: 'ERP Systems', status: 'missing', score: 50, target: 75 }],
            '/evidence/__TASK_ID__',
            '/status/__TASK_ID__',
            { status: {} },
            { hasTarget: true, targetId: 'target-1', skillEvidenceUrl: '/skill-evidence', skillEvidenceClearUrl: '/skill-evidence' },
        );
        state.selectSkill('ERP Systems');
        assert.equal(state.selectedSkill, 'ERP Systems');
        assert.equal(state.canUpload(state.selectedItem()), true);
    });

    it('submits link evidence through skill endpoint and polls task status', async () => {
        const calls = [];
        globalThis.fetch = async (url, options) => {
            calls.push([url, options]);
            if (url === '/skill-evidence') {
                return { ok: true, json: async () => ({ task: { id: 'task-erp', status: 'reviewing' }, evidence: { feedback: null } }) };
            }
            return { ok: true, json: async () => ({ status: 'revision_required', feedback: 'İsim uyuşmuyor' }) };
        };
        const state = skillPassport(
            [{ skill: 'SQL', status: 'missing', task_id: null, score: 40, target: 80 }],
            '/evidence/__TASK_ID__',
            '/status/__TASK_ID__',
            { status: {} },
            { hasTarget: true, targetId: 'target-1', skillEvidenceUrl: '/skill-evidence', skillEvidenceClearUrl: '/skill-evidence' },
        );
        state.init?.();
        state.selectSkill('SQL');
        state.evidence.kind = 'link';
        state.evidence.url = 'https://github.com/example/sql';
        await state.submitEvidence();
        assert.equal(state.items[0].status, 'revision');
        assert.equal(state.items[0].feedback, 'İsim uyuşmuyor');
        assert.equal(JSON.parse(calls[0][1].body).skill, 'SQL');
    });

    it('clears rejected evidence so user can re-upload', async () => {
        globalThis.fetch = async () => ({ ok: true, json: async () => ({ skill: 'SQL', task: { status: 'pending' } }) });
        const state = skillPassport(
            [{ skill: 'SQL', status: 'revision', evidence_ref: 'bad.pdf', feedback: 'Doğrulanamadı' }],
            '',
            '',
            { status: {} },
            { hasTarget: true, targetId: 'target-1', skillEvidenceUrl: '/skill-evidence', skillEvidenceClearUrl: '/skill-evidence' },
        );
        state.selectSkill('SQL');
        await state.clearEvidence();
        assert.equal(state.items[0].status, 'missing');
        assert.equal(state.items[0].evidence_ref, null);
    });
});
