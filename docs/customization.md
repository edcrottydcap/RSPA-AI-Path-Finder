# Customizing the widget

Everything editable lives in plain JavaScript objects and CSS variables inside `index.html`. No build step — edit, save, reload.

## The config constants (top of the `<script>` block)

```js
var HUB = "https://www.gorspa.org/";
var SHARE_TARGET = HUB + "ai-resource-hub/"; // where LinkedIn/email shares send people
var PING_URL = "";                            // optional response counter endpoint
```

- **`SHARE_TARGET`** — currently the AI Resource Hub. Once the quiz has its own public URL, point this at the quiz so shares drive people straight into taking it.
- **`PING_URL`** — the widget is zero-backend by default. To count completions, set this to any endpoint that accepts a POST; each completed quiz (never shared-link views) sends one anonymous JSON body: `{level, track, role, ts}`. Two free options:
  - **Formspree:** create a form, use its endpoint URL.
  - **Google Apps Script:** a 10-line `doPost` web app appending to a Sheet gives RSPA a live distribution of levels/tracks/roles.

## Questions — `QUESTIONS`

Six entries; each option contributes one thing:

```js
{label:"I use AI tools most days at work", pts:2}   // familiarity points (Q1, Q2, Q5)
{label:"Sales or marketing", role:"sales"}          // seat (Q3)
{label:"Run my company more efficiently", track:"i"} // track (Q4)
{label:"I don't know where to start", mod:-1, flag:"start"} // modifier (Q6)
```

Scoring: points from Q1+Q2+Q5 (0–9) plus the Q6 modifier, clamped to 0–9. Thresholds: 0–2 → 101, 3–5 → 102, 6–7 → 201, 8–9 → 301. If you add or remove point-carrying questions, adjust the thresholds and the "/ 9" subtotal copy to match the new maximum.

## Curriculum — `LEVELS` and `ELECTIVES`

Each level holds `grade`, `persona`, a one-line `line`, and five items:

```js
{sku:"TERM-101", t:"AI Terminology & Definitions",
 d:"Learn the language: ... decoded for the channel.",
 u:"https://www.gorspa.org/ai-terminology/"}
```

Adding, removing, or re-linking an item is a one-line change. The 201 lead item's description reads `__ROLE__`, which is replaced at render time by the visitor's seat-specific playbook line (`ROLE_PLAYBOOK`). `ELECTIVES` are appended when their condition fires (merchant track below 301, internal track at 101/102, or the "security worries" answer) and deduped against the level's items.

## Look and feel

All colors are CSS variables at the top of the `<style>` block, currently set to values measured from gorspa.org:

```css
--navy:#1f4a89; --navbar:#2860b3; --blue:#0066cc; --red:#c02b0a;
--page:#f1f1f1; --border:#d8d8d8; --paper:#fbf8f1; --ink:#2d2d2d;
```

Fonts are in `--disp` (UI) and `--mono` (register voice). The receipt's texture, the barcode, the stamp animation, and the printer-slot feed all live in clearly commented CSS sections.

## Social cards

The `<head>` carries Open Graph and Twitter meta tags. When the quiz gets its permanent URL, update `og:url`, and consider replacing `og:image` (currently the RSPA logo SVG) with a 1200×630 PNG "receipt" card — LinkedIn's crawler prefers raster images.

## Deep links

`?r=<level><track>` (e.g. `?r=301m`) reopens a result directly — levels `101|102|201|301`, tracks `f|p|i|m`. The shared view shows "— / 9" for the subtotal, relabels the buttons, and invites the viewer to take the quiz. These codes are stable, so they're safe to use in campaigns or QR codes.
