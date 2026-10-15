# How it was built

The AI Path Finder was designed and built as a single-file web widget, with the RSPA site itself as the design authority and the point-of-sale world as the creative concept.

## The concept

The audience — VARs, ISVs, vendors, and distributors in retail, restaurant, and grocery — lives at the register. So the quiz is a register: a status display tracks progress like a customer-facing readout ("ITEM 2/6 · SCANNING FAMILIARITY…"), answers ring up like line items, and the result prints as an itemized thermal receipt, complete with SKU codes for each curriculum item, a subtotal, a barcode, and a rubber-stamped persona. The receipt is the shareable artifact: it makes a placement quiz feel like something you'd pin to a corkboard rather than a form you filled out.

## Matching RSPA's brand

Rather than guessing at brand values, the palette was measured directly from the live gorspa.org stylesheet and computed styles:

| Token | Value | Source on gorspa.org |
|---|---|---|
| Navy | `#1F4A89` | Header text color |
| Nav blue | `#2860B3` | Main-menu bar background |
| Action blue | `#0066CC` | Links and buttons |
| Red | `#C02B0A` | Accent — used here only for the persona stamp |
| Gray band | `#F1F1F1` / `#D8D8D8` | Section backgrounds and borders |
| Text | `#333333` | Body copy |

Typography follows the site as well: the stack leads with **Proxima Nova** (RSPA's own face — it resolves automatically when the widget runs on gorspa.org), falling back to **Mulish** from Google Fonts, then Helvetica/Arial. **Spline Sans Mono** carries the register voice: the status display, receipt body, SKU codes, and micro-labels. Buttons are flat and squared, matching the site's controls. The official logo loads from `gorspa.org/wp-content/uploads/logo-color.svg`, with a typographic `R·S·P·A` fallback when the image can't load (offline preview, blocked domains).

## Architecture

Everything lives in one HTML file — markup, CSS, and vanilla JavaScript. There is no framework, no build step, and no backend. That was a deliberate choice: a single file is trivial to host anywhere (WordPress upload, GitHub Pages, a plain web root), can't break from a dependency update, and keeps the WordPress embed to a single iframe.

The content is data, not markup. Three JavaScript objects near the top of the script drive the whole experience:

- `QUESTIONS` — the six questions, their options, and what each option contributes (points, role, track, or a modifier).
- `LEVELS` — the four placements, each with a persona, a one-line reading, and a five-item curriculum of `{sku, title, description, url}` entries.
- `ELECTIVES` — extra items appended when they fit the visitor (track previews, IP-protection guardrails).

Scoring is intentionally simple and transparent: three questions contribute 0–3 points each (familiarity, vocabulary, company maturity), the final question nudges ±1, and thresholds at 0–2 / 3–5 / 6–7 / 8–9 map to 101 / 102 / 201 / 301. The goal question sets the track; the seat question personalizes the 201 curriculum and the receipt's "member seat" line. The full map is in [flow-spec.md](flow-spec.md).

## Details that matter

**Shareability.** Every result has a deep link (`?r=` + level + track code) that reprints the exact receipt for anyone who opens it — honestly: the shared view shows "— / 9" rather than fabricating the sharer's score, speaks in third person, and leads with "take the quiz yourself." LinkedIn and email shares are pre-drafted messages that name the placement and invite the reader to find their own path.

**Keyboard as part of the metaphor.** The A–D keycaps drawn on the answers are real: pressing A–D or 1–4 rings up the answer. A lock guards against double-presses during the advance animation.

**Accessibility.** Focus moves to each new question's heading so keyboard and screen-reader users aren't dropped back to the top; every control has a visible focus state; the status display is an `aria-live` region; `prefers-reduced-motion` collapses all animation including the receipt feed; contrast was checked against WCAG AA on every text/background pair.

**Embedding.** Inside an iframe, the widget posts its height to the parent (`{type:"rspa-pathfinder-height", height}`) on every screen change and resize, so the host page's iframe can grow as the receipt prints — no inner scrollbars. Clipboard, `history.replaceState`, and `postMessage` calls are all guarded for sandboxed-iframe environments, and a blocked clipboard shows the link in a toast instead of failing silently.

**Print texture, on purpose.** The receipt's faint horizontal banding and the barcode are CSS gradients — they are the subject (thermal paper and a barcode), not decoration.

## The WordPress plugin (v1.1)

The plugin wraps the same `index.html` rather than re-implementing it. A rewrite rule serves the quiz at `/ai-path-finder/` with settings injected as `window.RSPA_PATHFINDER_CONFIG`; every placement (block, `[pathfinder]` shortcode, widget, floating button, template tag) is an iframe of that URL. The decisions behind it:

- **Popup first.** The AI Resource Hub uses custom page templates, and nobody wants to re-lay-out a page for a quiz. A button or card that opens the quiz in a modal fits any page. The floating button prints from `wp_footer`, so it works even where a template ignores page content.
- **Look native.** The card is built from the hub's own parts, measured from the live theme: the `#1e73be` section bar of `.first-section-header`, the `#eeeeee` panel of `.ai-desc`, the `#005dac` title color of `.hub-title`, and the flat, square, uppercase `.btn-link.blue` button in `#275ca7`. Fonts inherit from the theme (Proxima Nova on gorspa.org).
- **No build step, again.** The block editor script is plain ES5 using `wp.element.createElement`, so the plugin folder is exactly what ships.
- **Result links come home.** Share links point back at the embedding page (the `host` parameter, validated to the site's own domain with `wp_validate_redirect`), and a `?r=` code there reopens that receipt, auto-opening the popup if the page has only a button.
- **Real phones, real theme.** Testing a copy of the live hub on an emulated iPhone showed the home page's header overflowing to ~825px, which stretches the layout viewport and pushes `position:fixed` elements off-screen. `embed.js` detects that and pins the floating button and popup to `visualViewport`.

## Progress tracking (v1.2)

The receipt was always a list of things to go get; v1.2 lets visitors check them off. The design constraints were the same as before: no backend, no accounts, no cookies. So progress lives in `localStorage`. The trick that makes the WordPress floating button aware of it is that the plugin serves the quiz from the *same site* as the hub pages: the quiz (in its iframe) and the host page share one storage area. The quiz writes the visitor's path and ticks; `embed.js` reads them, re-renders on the `storage` event when the popup changes something, and ticks off a hub page when the visitor lands on it. Prices are a receipt joke with a point: the subtotal makes the value of RSPA's free material visible, and the coupon line pays it off.

## QA

**v1.1 (plugin).** Tested on WordPress 7.1 (wp-env, PHP 8.2): every PHP file linted; the quiz route with and without pretty permalinks; the `host` parameter rejecting off-site URLs; launcher page matching; shared-link pass-through and auto-open; Esc and backdrop close; iframe grow and shrink on Retake. Screenshots in `docs/images/` were captured by Playwright from saved copies of the live gorspa.org pages with the plugin injected, at 1280px desktop and iPhone 13 emulation; the quiz showed zero horizontal overflow on phones.

**v1.0 (widget).**

The build went through iterated headless-browser rounds (desktop 800px and mobile 390px, plus the shared-link view) with full-page screenshots reviewed at each round, an automated design-quality scan, and an independent design review pass that audited every screen against the brief — its findings (focus management, shared-view honesty, clipboard failure feedback, mobile truncation, back-navigation state, and several visual-consistency items) were all fixed and re-verified, including a keyboard-only completion run.
