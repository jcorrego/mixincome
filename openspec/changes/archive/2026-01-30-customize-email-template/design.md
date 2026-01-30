# Design: customize-email-template

## Overview

Customize the 3 published Laravel mail template files to replace default Laravel branding with MixIncome identity. No PHP changes needed — Laravel's notification system automatically uses the published vendor views.

## Architecture Decisions

### AD-1: Inline SVG for Logo
**Decision:** Use inline SVG in the email header rather than a hosted image URL.
**Rationale:** Inline SVG works without external image hosting, doesn't get blocked by email clients that disable remote images by default, and renders at any size. The MixIncome "M in circle" logo is simple enough for inline SVG. Include a text fallback for clients that strip SVG.

### AD-2: Minimal Theme Changes
**Decision:** Only change brand-specific colors (buttons, header accent) and keep Laravel's proven email layout structure intact.
**Rationale:** Laravel's mail template is battle-tested across email clients. We only need to rebrand, not restructure.

## Files to Modify

### 1. `resources/views/vendor/mail/html/header.blade.php`
- Replace the Laravel logo conditional with MixIncome inline SVG logo + text
- Logo: "M" circle (matching `app-logo-icon.blade.php`) rendered as inline SVG
- Fallback: text "MixIncome" if SVG not supported
- Link wraps to `{{ $url }}`

### 2. `resources/views/vendor/mail/html/footer.blade.php`
- Replace `{{ $slot }}` default with branded footer
- Content: `© {year} MixIncome. All rights reserved.`
- Keep the Markdown parsing for any additional slot content

### 3. `resources/views/vendor/mail/html/themes/default.css`
- `.header` background: transparent or subtle slate
- `.button-primary` background: `#1e293b` (slate-800)
- `.button-primary` hover: `#334155` (slate-700)
- Link color: `#1e293b`
- Keep all other styles unchanged

## Brand Colors Reference
- **Primary (buttons, links):** `#1e293b` (slate-800)
- **Accent:** `#f59e0b` (amber-500) — for subtle highlights if needed
- **Text:** `#52525b` (zinc-600) — keep Laravel default, it works
- **Footer text:** `#9ca3af` (gray-400)
