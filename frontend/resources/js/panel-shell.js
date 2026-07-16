import { Alpine, Livewire } from '../../vendor/livewire/livewire/dist/livewire.esm';
import { initMarketingMotion } from './marketing-motion';

window.Alpine = Alpine;

function bootPanelShell() {
    if (!window.__panelShellLivewireStarted) {
        window.__panelShellLivewireStarted = true;
        Livewire.start();
    }

    initMarketingMotion();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootPanelShell, { once: true });
} else {
    bootPanelShell();
}

document.addEventListener('livewire:navigated', () => {
    initMarketingMotion();
});
