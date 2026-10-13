# Keeping the content current

The RSPA AI Resource Hub is updated frequently; the widget's curriculum is a snapshot. Two tools keep them in sync — a script that reports drift, and a ready-made prompt that lets an AI assistant do the whole update pass.

## 1. The audit script

```bash
node scripts/audit-content.mjs
```

Node 18+ and normal internet access; nothing to install. The script reads `index.html` and prints a three-part report:

- **Link health.** Every gorspa.org and Google Drive link the quiz uses must resolve. This catches renamed or removed pages, and the script exits non-zero on breakage so it can gate CI.
- **New on the hub.** Content links on the hub's seven pages that the quiz doesn't use yet: blog posts, section pages, uploaded PDFs/Word/PowerPoint files, and Google Drive decks.
- **Summary.**

Links that were reviewed and deliberately left off the receipt live in the `REVIEWED` list at the top of the script, each with a one-line reason. The audit counts them but doesn't re-report them, so "New on the hub" shows only genuinely new content. Delete an entry to have it raised again.

It already runs automatically: `.github/workflows/content-audit.yml` runs it on the first of every month (and on demand from the Actions tab), saves the report as a build artifact, and opens a GitHub issue when a link breaks or new content appears.

## The monthly routine

1. Read the audit report (Actions tab → *Content audit*, or run it locally).
2. For each **NEW** item, decide where it belongs: on a receipt (edit `LEVELS` / `ELECTIVES` / `ROLE_ELECTIVES` in `index.html`) or in `REVIEWED` with a reason.
3. Bump `rspa-pathfinder-content-date` (and the version) in `index.html`'s `<head>`, and update `docs/flow-spec.md`.
4. `node scripts/build-plugin.mjs`, then `node scripts/audit-content.mjs` until it reports zero broken and zero new.
5. Commit and push. GitHub Pages updates itself. Tag a release (`git tag v1.x.y && git push --tags`) to publish a new plugin zip, then upload it on the WordPress site (**Replace current with uploaded**).

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
> 6. After approval, apply the edits to `index.html` only within the data objects (`QUESTIONS`, `LEVELS`, `ELECTIVES`, `ROLE_ELECTIVES`, `ROLE_PLAYBOOK`); add every hub item you deliberately did *not* use to `REVIEWED` in `scripts/audit-content.mjs` with a one-line reason; bump `rspa-pathfinder-content-date` and `rspa-pathfinder-version` in `index.html`'s `<head>`; update `docs/flow-spec.md` (including its "What changed" list) and `CHANGELOG.md`; run `node scripts/build-plugin.mjs`; and run `node scripts/audit-content.mjs` to confirm zero broken links and zero new candidates.
> 7. Keep the receipt's personality intact: SKU-style codes (e.g. `TERM-101`), the register voice, and the "TOTAL DUE $0.00" gag are part of the product.

---

## What "current" means here

The widget doesn't need to link *everything* on the hub; it needs the five best next steps per level. A good monthly pass usually changes one or two items, most often the conference (RetailNOW, Inspire) and podcast slots, which age fastest. The six hub section pages themselves are stable anchors and rarely change. Prefer direct PDF links over section pages when an item points at one specific resource, and RSPA-hosted files over Google Drive links.
