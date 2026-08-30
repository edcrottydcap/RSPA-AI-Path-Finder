# RSPA AI Path Finder

An interactive placement quiz for the [RSPA AI Resource Hub](https://www.gorspa.org/ai-resource-hub/). Six quick questions place a visitor on an **AI 101 / 102 / 201 / 301** track with a persona (The Explorer, The Practitioner, The Operator, The Trailblazer) — then their personalized curriculum prints as a point-of-sale **receipt**, itemized with live links to RSPA content. Built for North America's retail IT channel: VARs, ISVs, vendors, and distributors in the retail, restaurant, and grocery verticals.

**[▶ Live preview](https://claude.ai/code/artifact/5fded164-407c-409f-abd7-189eb3e8c3a5)** · [Placement logic](docs/placement-logic.md) · [How it was built](docs/how-it-was-built.md) · [WordPress embed guide](docs/wordpress-embed.md) · [Customization](docs/customization.md) · [Content maintenance](docs/content-maintenance.md) · [Full flow spec](docs/flow-spec.md)

## What it does

The visitor answers six questions — AI familiarity, a vocabulary check, their seat (exec / sales / support / technical), their goal, their company's AI maturity, and what's holding them back. A 0–9 familiarity score plus a goal-based track places them at one of four levels, and the receipt prints a five-item curriculum drawn from the RSPA AI Resource Hub, personalized by role and track, with electives appended when they fit (for example, IP-protection guardrails for anyone who flagged security worries). The full decision flow is diagrammed in [docs/placement-logic.md](docs/placement-logic.md) — three questions score familiarity from different angles, one nudges the result honestly, and the other two steer *what* the curriculum contains rather than how advanced it is.

Results are shareable three ways: a **copy-link** deep link (`?r=201i`) that reopens the exact receipt, a pre-filled **LinkedIn** post, and a drafted **email** — the latter two invite readers to find their own path on the hub. Questions can be answered by mouse, touch, or the **A–D / 1–4 keys**.

## Quick start

The entire widget is one self-contained file: [`index.html`](index.html). No build step, no dependencies, no backend.

- **Try it locally:** download `index.html` and open it in any browser.
- **Embed it on WordPress:** see the [WordPress embed guide](docs/wordpress-embed.md) — upload the file, paste a ten-line iframe snippet into a Custom HTML block, done. The widget auto-reports its height so the iframe resizes itself.
- **Host it on GitHub Pages:** Settings → Pages → deploy from `main`, and the quiz is live at `https://<owner>.github.io/RSPA-AI-Path-Finder/` — a ready-made URL to link from the hub or iframe into WordPress.

## Design

The widget is styled to the RSPA site itself: white card on the site's light gray, RSPA navy headings, the nav-bar blue as the register's status display, action-blue buttons, and RSPA red reserved for the persona stamp. The official RSPA logo loads from gorspa.org with a text fallback. The one theatrical moment is deliberate: the receipt feeds out of a printer slot and the persona stamps on — a nod to the point-of-sale world this community lives in ("TOTAL DUE: $0.00 · FEAR OF AI −100% · NO REFUNDS ON KNOWLEDGE").

Accessibility is built in: real buttons with visible focus states, keyboard-only completion, focus management between questions, `prefers-reduced-motion` support, and WCAG-checked contrast.

## Repository layout

| Path | What it is |
|---|---|
| `index.html` | The complete widget — the only file you deploy |
| `docs/placement-logic.md` | The decision flow, as Mermaid diagrams |
| `docs/how-it-was-built.md` | Architecture, design decisions, and QA notes |
| `docs/wordpress-embed.md` | Step-by-step embedding options for gorspa.org |
| `docs/customization.md` | Editing questions, curriculum links, share targets, and the optional response counter |
| `docs/content-maintenance.md` | Keeping the curriculum current: the audit script + an AI maintainer prompt |
| `docs/flow-spec.md` | Every question, the scoring model, and the full curriculum map |
| `scripts/audit-content.mjs` | Checks the widget's links against the live hub and reports new content (Node 18+, no deps) |

## Keeping it current

`node scripts/audit-content.mjs` verifies every curriculum link still resolves and lists hub content the widget doesn't reference yet; [docs/content-maintenance.md](docs/content-maintenance.md) pairs it with a ready-made prompt for an AI assistant with browsing, which can run the whole update pass — inventory the hub, verify links, and propose curriculum changes level by level.

## Content

All curriculum links point to RSPA-published resources on [gorspa.org](https://www.gorspa.org/ai-resource-hub/) and were verified live at build time. Content belongs to the Retail Solutions Providers Association; suggestions for the hub go to Membership@GoRSPA.org.

---

Maintained by [Ed Crotty](https://github.com/edcrottydcap). Contributions by issue, please — the repo is read-only for everyone else for now.
