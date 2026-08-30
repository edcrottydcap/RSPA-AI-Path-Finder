# Keeping the content current

The RSPA AI Resource Hub is updated frequently; the widget's curriculum is a snapshot. Two tools keep them in sync — a script that reports drift, and a ready-made prompt that lets an AI assistant do the whole update pass.

## 1. The audit script

```bash
node scripts/audit-content.mjs
```

Node 18+ and normal internet access — nothing to install. The script reads `index.html`, extracts every gorspa.org link the widget uses, and prints a three-part report: **Link health** (does every curriculum link still resolve — catches renamed or removed pages, exits non-zero on breakage so it can gate CI), **New on the hub** (content links found on the hub's seven pages that the widget doesn't reference yet — new blog posts, PDFs, and section pages are the usual finds), and a **Summary**.

Run it monthly, or wire it into a scheduled GitHub Action that opens an issue when something breaks or appears:

```yaml
# .github/workflows/content-audit.yml
name: Content audit
on:
  schedule: [{cron: "0 12 1 * *"}]   # first of the month
  workflow_dispatch:
jobs:
  audit:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - run: node scripts/audit-content.mjs | tee audit-report.md
      - uses: actions/upload-artifact@v4
        if: always()
        with: {name: audit-report, path: audit-report.md}
```

The script reports; a human (or the prompt below) decides. Placement quality depends on editorial judgment about *where* a new resource belongs, which a link diff can't make.

## 2. The AI maintainer prompt

For a fuller pass — including reading the new content and deciding where it fits — paste this prompt into an AI assistant that has a browser or web-fetch tool (Claude with web access, or a coding agent in this repo). It works standalone; attach or point it at `index.html`.

---

> You are maintaining the RSPA AI Path Finder, a single-file quiz widget (`index.html` in github.com/edcrottydcap/RSPA-AI-Path-Finder) that places visitors at AI 101/102/201/301 and prints a curriculum of links from the RSPA AI Resource Hub.
>
> **Your task: bring its content up to date with the live hub, without changing the design or the scoring model.**
>
> 1. Read the current curriculum data in `index.html`: the `QUESTIONS`, `LEVELS`, and `ELECTIVES` objects, plus the level thresholds in `tally()`. `docs/placement-logic.md` and `docs/flow-spec.md` describe the intent.
> 2. Browse the hub and all its section pages: gorspa.org/ai-resource-hub/, /ai-terminology/, /ai-tools/, /ai-internal-applications/, /ai-merchant-facing-applications/, /ai-misconceptions-best-practices/, /rspa-ai-education-content/. Inventory every content link (section pages, blog/podcast posts, uploaded PDFs, embedded videos), noting for each: title, one-line summary, audience level (beginner → advanced), and topic (foundations / prompting / internal ops / merchant-facing / governance).
> 3. Verify every URL currently in `LEVELS` and `ELECTIVES` still resolves. Replace any that 404 or redirect with their successor page.
> 4. Propose curriculum changes as a table: level → item to add/replace/remove → URL → one-line rationale. Rules: each level keeps exactly five spine items; each item's description stays under 120 characters, written in the widget's voice (plain, channel-specific, no hype); prefer RSPA-hosted content over third-party links; never fabricate a URL — only link pages you actually opened; the 301 track leads merchant-facing, 201 internal, 102 prompting skill, 101 foundations.
> 5. If the hub has grown a genuinely new content *category* (for example a new vertical or a certification), propose — separately, not silently — either a new elective or a new track, with the exact `QUESTIONS`/`ELECTIVES` entries it would need. Do not add questions without flagging that the "/ 9" scoring copy and thresholds must be reviewed.
> 6. After approval, apply the edits to `index.html` only within the data objects (`QUESTIONS`, `LEVELS`, `ELECTIVES`, `ROLE_PLAYBOOK`), update `docs/flow-spec.md` to match, and run `node scripts/audit-content.mjs` to confirm zero broken links and zero unreferenced major content.
> 7. Keep the receipt's personality intact: SKU-style codes (e.g. `TERM-101`), the register voice, and the "TOTAL DUE $0.00" gag are part of the product.

---

## What "current" means here

The widget doesn't need to link *everything* on the hub — it needs the five best next steps per level. A good monthly pass usually changes one or two items, most often the Inspire-deck and podcast slots, which age fastest. The six hub section pages themselves are stable anchors and rarely change.
