# CareerTalent Workspace Shell Style Guide

## Source audit

- Reference surface: local authenticated `/admin` shell.
- Implementation surface: local authenticated `/company` shell.
- Viewports: `1440x1000` desktop, `390x844` mobile.
- Captured states: admin dashboard, company dashboard, company mobile sidebar open.
- Evidence:
  - `output/playwright/admin-shell-source.png`
  - `output/playwright/admin-shell-reference-after-shared.png`
  - `output/playwright/company-shell-desktop.png`
  - `output/playwright/company-shell-mobile-menu.png`
- Pixel gate: admin shell before/after shared-partial extraction is `AE=0` after masking only the live clock/date region (`x=1000..1439`, `y=0..60`).
- Company versus admin full-page pixels are intentionally not zero: business content and the existing teal company palette differ. Shell geometry must differ by `0px`.

## Design tokens

### Colors

| Token | Value | Usage |
|---|---:|---|
| `--company-accent` | `#0f766e` | Company active controls and primary actions |
| `--company-accent-hover` | `#115e59` | Company primary hover |
| `--company-brand` | `#10b981` | Existing company brand signal |
| `--company-accent-ink-dark` | `#5eead4` | Company accent text in dark mode |
| `--admin-accent` | `#ffbd72` | Admin reference identity; unchanged |
| `--workspace-accent-*` | shell alias | Shared header state colors resolved by the parent shell |

Company colors are fixed. Shell parity must not replace them with admin colors.

### Typography

| Token | Value | Usage |
|---|---:|---|
| `--font-sans` | Instrument Sans | Shell and product UI |
| Sidebar navigation | `14px` | Shared nav items |
| Group label | `12px / 600` | Uppercase sidebar group labels |
| Admin brand | `18px / 700 / 28px` | Reference brand |
| Company brand | `17px / 700 / 28px` | Same line box; compact tracking prevents the longer label wrapping |

## Spacing, radius, and layout

| Rule | Value |
|---|---:|
| Desktop sidebar | `256px` |
| Desktop header | `61px` |
| Sidebar inner padding | `24px` |
| Brand-to-nav gap | `32px` |
| Desktop content padding | `40px` |
| Mobile content padding | `24px` |
| Nav radius | `8px` |
| Profile card radius | `12px` |
| Mobile sidebar z-index | `40` |

Desktop shell geometry:

- Sidebar: `x=0, y=0, w=256, h=viewport`.
- Header: `x=256, y=0, w=viewport-256, h=61`.
- Main: `x=256, y=61, w=viewport-256, h=viewport-61`.
- Mobile: header remains `61px`; sidebar overlays at `256px`; backdrop closes on click and `Escape` closes the shell.

## Components

### Shared header

- Blade: `frontend/resources/views/workspace/partials/header.blade.php`.
- Structure: mobile menu, API state, live date/time, theme, notification, language.
- Accent selectors: `.workspace-header`, `.workspace-accent-dot`, `.workspace-accent-pill`, `.workspace-lang-active`.
- Runtime identity comes from `.admin-shell` or `.company-shell`; markup and spacing stay shared.

### Shared sidebar navigation

- Blade: `frontend/resources/views/workspace/partials/sidebar-nav.blade.php`.
- Icons: Lucide through `frontend/resources/views/app/partials/sidebar-nav-icon.blade.php`.
- Structure: uppercase group label, `8px` rounded nav row, `16px` icon, active soft-accent state.
- Company-specific organization switcher stays in the footer; it does not move the primary nav start position.

### Profile and logout

- Same card geometry, avatar size (`40px`), typography, separators, and logout row as admin.
- Routes and user data remain workspace-specific.

## CSS usage rule

- Canonical style surface: `frontend/resources/css/app.css`.
- Shared shell structure lives only in `workspace/partials`; workspace views pass data and routes.
- Palette values remain in parent shell tokens. Do not hardcode admin amber into company components.
- New sidebar/header visuals must extend the shared partials and workspace token aliases, not add raw glyphs or a second header implementation.

## Verification

| Screen | Reference | Current | Diff | Status |
|---|---|---|---:|---|
| Admin shell refactor | pre-extraction admin capture | `admin-shell-reference-after-shared.png` | `AE=0` with dynamic clock masked | Pass |
| Company desktop geometry | `/admin` `1440x1000` | `/company` `1440x1000` | `0px` on sidebar/header/main bounds | Pass |
| Company mobile menu | admin mobile contract | `/company` `390x844` | `0px` overflow; `256px` overlay; `61px` header | Pass |
