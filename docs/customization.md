# Customizing the widget

Everything editable lives in plain JavaScript objects and CSS variables inside `index.html`. No build step — edit, save, reload.

## The config constants (top of the `<script>` block)

```js
var CFG = window.RSPA_PATHFINDER_CONFIG || {};               // set by the WordPress plugin
var HUB = "https://www.gorspa.org/";
var SHARE_TARGET = CFG.shareTarget || HUB + "ai-resource-hub/"; // where LinkedIn/email shares send people
var PING_URL = CFG.pingUrl || "";                               // optional response counter endpoint
var RESULT_BASE = CFG.resultBase || (location.origin + location.pathname); // base of "Copy my result link"
var COUPON = String(CFG.coupon || "PATHFINDER").toUpperCase();              // coupon on the receipt
```

**On WordPress, don't edit these.** Set them in **Settings → AI Path Finder** and the plugin injects `window.RSPA_PATHFINDER_CONFIG` ([wordpress-plugin.md](wordpress-plugin.md)). The defaults apply to the standalone file and GitHub Pages.

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

## Curriculum: `LEVELS`, `ELECTIVES`, `ROLE_ELECTIVES`

Each level holds `grade`, `persona`, a one-line `line`, and five items:

```js
{sku:"TERM-101", t:"AI Terminology & Definitions",
 d:"Learn the language: ... decoded for the channel.",
 u:"https://www.gorspa.org/ai-terminology/"}
```

Adding, removing, or re-linking an item is a one-line change. The 201 lead item's description reads `__ROLE__`, which is replaced at render time by the visitor's seat-specific playbook line (`ROLE_PLAYBOOK`).

Electives are appended after the five items and deduped against them:

| Object | Key | Fires when |
|---|---|---|
| `ELECTIVES` | `m` | merchant track, below 301 |
| `ELECTIVES` | `voice` | merchant track, at 301 |
| `ELECTIVES` | `i` | internal track, at 101/102 |
| `ELECTIVES` | `risk` | Q6 = security/IP worries |
| `ROLE_ELECTIVES` | `tech`, `exec` | the visitor's seat (Q3); not shown on shared links, which don't carry a seat |

To add a seat elective for `sales` or `ops`, add one entry to `ROLE_ELECTIVES`; no other code changes.

**After any edit, run `node scripts/build-plugin.mjs`** so the WordPress plugin gets the same content, and bump the `rspa-pathfinder-version` / `rspa-pathfinder-content-date` meta tags at the top of `index.html`.

## Prices and the coupon

Each receipt line shows a "value". By default it comes from the SKU prefix via `PRICE`:

```js
var PRICE = {TERM:49, MYTH:49, PRMPT:49, CRAFT:79, SEC:59, ACT:19, POD:29, PDF:39, DECK:49, OPS:79, MRCH:99, CTA:249};
```

Give any item its own value with `p:`, as the electives do (`{sku:"ELEC-M", p:99, …}`). The totals print `SUBTOTAL · N ITEMS`, then `COUPON <code> · 100% OFF` for the same amount, then `TOTAL DUE $0.00`. The coupon code comes from the plugin's **Coupon code** setting (or `CFG.coupon`).

## The RSPA reward

`CFG.reward` (set by the plugin's **RSPA reward** settings) turns on a discount-code coupon under the receipt totals:

```js
window.RSPA_PATHFINDER_CONFIG = { reward: {
  code: "AIPATH15", headline: "15% off RetailNOW registration",
  details: "New registrations only.", url: "https://…", cta: "Register now",
  unlock: "complete",   // "complete" | "count" | "receipt"
  count: 3              // used when unlock is "count"
}};
```

It unlocks from the same picked-up count as the checklist. The plugin passes the same object to the floating button (`data-reward` on the launcher), so both agree on locked or unlocked.

## The picked-up checklist

Every receipt line has a checkbox that ticks when the visitor opens it, plus a `PICKED UP n / N` total. Ticks are saved in `localStorage` under `rspa-pathfinder-picked-up-v2` (one entry per receipt line, `SKU|URL` → timestamp), so they survive retakes and return visits, and a "clear them" link resets them. The visitor's own result is also saved under `rspa-pathfinder-path` (level, persona, and the receipt's lines), which the WordPress plugin's floating button reads to show progress. If storage is blocked, boxes still tick for the visit and nothing breaks. Shared links (`?r=`) never overwrite the viewer's own path.

## Look and feel

All colors are CSS variables at the top of the `<style>` block, currently set to values measured from gorspa.org:

```css
--navy:#1f4a89; --navbar:#2860b3; --blue:#0066cc; --red:#c02b0a;
--page:#f1f1f1; --border:#d8d8d8; --paper:#fbf8f1; --ink:#2d2d2d;
```

Fonts are in `--disp` (UI) and `--mono` (register voice). The receipt's texture, the barcode, the stamp animation, and the printer-slot feed all live in clearly commented CSS sections.

## Social cards

The `<head>` carries Open Graph and Twitter meta tags. The WordPress plugin rewrites `og:url` to the quiz's address and, if you set **Social card image**, `og:image` too. For a standalone copy, edit them by hand. A 1200×630 PNG "receipt" card works best, because LinkedIn's crawler prefers raster images over the logo SVG.

## Deep links

`?r=<level><track>` (e.g. `?r=301m`) reopens a result directly — levels `101|102|201|301`, tracks `f|p|i|m`. The shared view shows "— / 9" for the subtotal, relabels the buttons, and invites the viewer to take the quiz. These codes are stable, so they're safe to use in campaigns or QR codes.
