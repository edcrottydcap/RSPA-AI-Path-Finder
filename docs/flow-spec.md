# RSPA AI Path Finder — Flow Spec & Embed Guide

An interactive placement quiz for the [RSPA AI Resource Hub](https://www.gorspa.org/ai-resource-hub/). Six questions place a visitor on an **AI 101 / 102 / 201 / 301** track and print a personalized, shareable "curriculum receipt" with live links to RSPA content. Styled to RSPA's site (Proxima Nova–first font stack, RSPA navy `#1F4A89`, nav blue `#2860B3`, action blue `#0066CC`, RSPA red `#C02B0A` for the stamp, official logo from `gorspa.org/wp-content/uploads/logo-color.svg`).

**Why a custom widget instead of Typeform:** zero cost, no response caps, full branching control, a rich multi-link results page (Typeform endings can't do itemized link lists well), and it embeds on WordPress with one iframe. Typeform remains a fine alternative if you want hosted response analytics — this doc's question/scoring spec can be rebuilt there 1:1 via your Typeform MCP connector.

---

## The six questions

| # | Question | Options → effect |
|---|----------|------------------|
| 1 | **How would you describe you and AI right now?** | Never introduced (0) · Dabbled with ChatGPT (1) · Use AI most days (2) · Building/deploying AI (3) |
| 2 | **Someone says "RAG" in a meeting. You think…** (vocabulary check) | Cleaning cloths? (0) · Heard it (1) · Can explain it (2) · Implemented one (3) |
| 3 | **Which seat do you sit in most days?** | Owner/exec · Sales/marketing · Support/ops · Technical — sets the **role** (flavors the 201 curriculum + receipt "member seat") |
| 4 | **What would make this worth your time?** | Understand AI (Foundations) · Better results from tools (Practitioner) · Run company efficiently (Internal) · Bring AI to merchants (Merchant) — sets the **track** |
| 5 | **How does your company use AI today?** | Not at all (0) · Individuals experimenting (1) · Approved tools + rules (2) · Built into products (3) |
| 6 | **What's really holding you back?** | Don't know where to start (−1) · Security/IP worries (adds guardrails elective) · No time (0) · Nothing, advanced stuff (+1) |

## Scoring & classification

`familiarity = Q1 + Q2 + Q5` (0–9), then Q6 modifier (±1, clamped 0–9):

| Adjusted score | Level | Persona |
|---|---|---|
| 0–2 | **AI 101** | The Explorer |
| 3–5 | **AI 102** | The Practitioner |
| 6–7 | **AI 201** | The Operator |
| 8–9 | **AI 301** | The Trailblazer |

## Curriculum per level (all links verified live — last refresh 2026-10-13)

**AI 101 — The Explorer** (build the foundation)

1. `TERM-101` AI Terminology & Definitions — https://www.gorspa.org/ai-terminology/
2. `MYTH-101` AI Misconceptions & Best Practices — https://www.gorspa.org/ai-misconceptions-best-practices/
3. `PRMPT-101` Basic Prompting / Cheat Sheet — https://www.gorspa.org/ai-tools/
4. `POD-151` Podcast ep. 151, Byron Reese — https://www.gorspa.org/blog-rspa-trusted-advisor-ep-151-ais-impact-with-tech-futurist-byron-reese/
5. `ACT-101` Action item: one tool, one task this week — https://www.gorspa.org/ai-misconceptions-best-practices/

**AI 102 — The Practitioner** (level up prompting)

1. `PRMPT-102` Prompting Cheat Sheet & Few-Shot — https://www.gorspa.org/ai-tools/
2. `CRAFT-102` Advanced Prompting: C.R.A.F.T., personas, chain-of-thought — https://www.gorspa.org/ai-tools/
3. `SEC-102` Protect Your IP — https://www.gorspa.org/ai-misconceptions-best-practices/
4. `PDF-102` 7-Day Social Content Pro Prompt (PDF) — https://www.gorspa.org/wp-content/uploads/7-Day-Social-Media-content-and-Image-Generator-Pro-Prompt.pdf
5. `POD-153` Podcast ep. 153, Zack Kass — https://www.gorspa.org/blog-rspa-trusted-advisor-ep-153-how-ai-can-expand-var-isv-potential-with-zack-kass/

**AI 201 — The Operator** (deploy across the business; item 1's description is personalized by the Q3 seat)

1. `OPS-201` Internal AI Applications, by Team — https://www.gorspa.org/ai-internal-applications/
2. `PDF-201` Top 10 AI Use Cases for IT Resellers (PDF) — https://www.gorspa.org/wp-content/uploads/Top-10-AI-Use-Cases-for-IT-Resellers-Internal-Productivity-Business-Growth.pdf
3. `PDF-202` Stop Talking About AI and Start Using It (PDF) — https://www.gorspa.org/wp-content/uploads/Stop-Talking-About-AI-and-Start-Using-It-A-Practical-Application-for-Busy-Channel-Executives.pdf
4. `SEC-201` Governance Guardrails — https://www.gorspa.org/ai-misconceptions-best-practices/
5. `PDF-203` What VAR & ISV Teams Should Learn Next About AI (RetailNOW 2026, PDF) — https://www.gorspa.org/wp-content/uploads/What-VAR-ISV-Teams-Should-Learn-Next-About-AI.pdf

**AI 301 — The Trailblazer** (merchant-facing & strategic)

1. `MRCH-301` Merchant-Facing AI, by Vertical — https://www.gorspa.org/ai-merchant-facing-applications/
2. `POD-159` Podcast ep. 159, AI & Payments Opportunities — https://www.gorspa.org/blog-rspa-trusted-advisor-ep-159-ai-payments-opportunities-for-isos-agents-isvs-and-vars/
3. `PDF-301A` Conversational AI in Retail: Case Studies (PDF) — https://www.gorspa.org/wp-content/uploads/Conversational_AI_Case_Studies.pdf
4. `PDF-301B` How Restaurants, Retailers & C-Stores Use AI (PDF) — https://www.gorspa.org/wp-content/uploads/AI-Transforming-Sales-and-Operations.pdf
5. `CTA-301` Join the RSPA AI Advisory Group — mailto:Membership@GoRSPA.org

**Electives (auto-appended after the five, deduped):**

| SKU | Elective | Fires when | Link |
|---|---|---|---|
| `ELEC-M` | Merchant-facing AI preview | Track = merchant, level below 301 | https://www.gorspa.org/ai-merchant-facing-applications/ |
| `ELEC-V` | Voice AI in Retail IT (Inspire 2026 deck, .pptx) | Track = merchant, level 301 | https://drive.google.com/uc?export=download&id=167g3m_W9bVML_R7xXnJOEFtqOKq_wZOw |
| `ELEC-I` | Internal AI preview | Track = internal, level 101/102 | https://www.gorspa.org/ai-internal-applications/ |
| `ELEC-S` | IP & accuracy guardrails | Q6 = security/IP worries | https://www.gorspa.org/ai-misconceptions-best-practices/ |
| `ELEC-T` | AI-Assisted Development (RetailNOW 2026, PDF) | Seat = technical | https://www.gorspa.org/wp-content/uploads/The-Latest-AI-Enablers-for-ISVs-FINAL.pdf |
| `ELEC-X` | AI Considerations for VAR Leaders (Inspire 2026 deck, .pptx) | Seat = owner/executive | https://drive.google.com/uc?export=download&id=1Q1Ds4-gEFKq4kEenIg5SFWdfA9tV9MkH |

Seat electives (`ELEC-T`, `ELEC-X`) need the visitor's own answers, so they don't appear on shared-link views (`?r=`), which carry only level and track.

### What changed on 2026-10-13

- 102: `DECK-102` (link to the education section page) → `PDF-102`, the hub's 7-Day Social Content prompt — a hands-on prompting exercise.
- 201: `POD-153` was duplicated from 102 → `PDF-203`, the RetailNOW 2026 "What VAR & ISV Teams Should Learn Next" guide.
- 301: `DECK-301` and `OPS-301` (an internal-ops item in the merchant level) → `PDF-301A` Conversational AI case studies and `PDF-301B` merchant use cases by vertical.
- New electives `ELEC-V`, `ELEC-T`, `ELEC-X`. The Voice AI deck that `DECK-301` used to point at now lands as `ELEC-V` for merchant-track 301s.
- Every other hub item was reviewed and recorded, with the reason, in `REVIEWED` in `scripts/audit-content.mjs`.

## Shareable results

**Copy link** shares a deep link like `…?r=201i` (level + track code: f/p/i/m); opening it shows that receipt immediately with a "take the quiz yourself" prompt (score shown as "— / 9" since it isn't the viewer's). **Share on LinkedIn** opens a pre-filled post naming the placement and pointing readers to the RSPA AI Resource Hub; **Email it** opens a drafted email doing the same. Both read from the `SHARE_TARGET` constant — currently the hub URL; point it at the quiz's own URL once hosted. A–D or 1–4 keys answer questions from the keyboard.

## Optional response counter

The widget is zero-backend by default. To count completions, set the `PING_URL` constant to any endpoint (Formspree, Google Apps Script web app, etc.); each completed quiz then sends one anonymous JSON POST: `{level, track, role, ts}`. Shared-link views are never counted.

## Embedding on WordPress

Use the plugin in `wordpress-plugin/` — block, `[pathfinder]` shortcode, floating button, or its own page. See [wordpress-plugin.md](wordpress-plugin.md). A no-plugin iframe option is in [wordpress-embed.md](wordpress-embed.md).

## Customizing

All questions, scoring, curriculum items, and links live in plain JS objects near the top of the `<script>` block (`QUESTIONS`, `LEVELS`, `ELECTIVES`, `ROLE_ELECTIVES`). Editing a link or adding a sixth curriculum item is a one-line change. Colors are CSS variables at the top of the `<style>` block, set to RSPA brand values.
