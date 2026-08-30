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

## Curriculum per level (all links verified live)

**AI 101 — The Explorer** (build the foundation)

1. AI Terminology & Definitions — https://www.gorspa.org/ai-terminology/
2. AI Misconceptions & Best Practices — https://www.gorspa.org/ai-misconceptions-best-practices/
3. Basic Prompting / Cheat Sheet — https://www.gorspa.org/ai-tools/
4. Podcast ep. 151, Byron Reese — https://www.gorspa.org/blog-rspa-trusted-advisor-ep-151-ais-impact-with-tech-futurist-byron-reese/
5. Action item: one tool, one task this week — https://www.gorspa.org/ai-misconceptions-best-practices/

**AI 102 — The Practitioner** (level up prompting)

1. Prompting Cheat Sheet & Few-Shot — https://www.gorspa.org/ai-tools/
2. Advanced Prompting: C.R.A.F.T., personas, chain-of-thought — https://www.gorspa.org/ai-tools/
3. Protect Your IP — https://www.gorspa.org/ai-misconceptions-best-practices/
4. Inspire 2026 AI session decks — https://www.gorspa.org/rspa-ai-education-content/
5. Podcast ep. 153, Zack Kass — https://www.gorspa.org/blog-rspa-trusted-advisor-ep-153-how-ai-can-expand-var-isv-potential-with-zack-kass/

**AI 201 — The Operator** (deploy across the business; item 1's description is personalized by the Q3 role)

1. Internal AI Applications, by Team — https://www.gorspa.org/ai-internal-applications/
2. Top 10 AI Use Cases for IT Resellers (PDF) — https://www.gorspa.org/wp-content/uploads/Top-10-AI-Use-Cases-for-IT-Resellers-Internal-Productivity-Business-Growth.pdf
3. Stop Talking About AI and Start Using It (PDF) — https://www.gorspa.org/wp-content/uploads/Stop-Talking-About-AI-and-Start-Using-It-A-Practical-Application-for-Busy-Channel-Executives.pdf
4. Governance Guardrails — https://www.gorspa.org/ai-misconceptions-best-practices/
5. Podcast ep. 153, Zack Kass — (link above)

**AI 301 — The Trailblazer** (merchant-facing & strategic)

1. Merchant-Facing AI, by Vertical — https://www.gorspa.org/ai-merchant-facing-applications/
2. Podcast ep. 159, AI & Payments Opportunities — https://www.gorspa.org/blog-rspa-trusted-advisor-ep-159-ai-payments-opportunities-for-isos-agents-isvs-and-vars/
3. Voice AI & Predictive Analysis decks — https://www.gorspa.org/rspa-ai-education-content/
4. Cross-System Pipeline Intelligence — https://www.gorspa.org/ai-internal-applications/
5. Join the RSPA AI Advisory Group — mailto:Membership@GoRSPA.org

**Electives (auto-appended, deduped):** Merchant track below 301 → merchant-facing page preview; Internal track at 101/102 → internal-applications preview; "security/IP worries" on Q6 → IP & Accuracy Guardrails.

## Shareable results

**Copy link** shares a deep link like `…?r=201i` (level + track code: f/p/i/m); opening it shows that receipt immediately with a "take the quiz yourself" prompt (score shown as "— / 9" since it isn't the viewer's). **Share on LinkedIn** opens a pre-filled post naming the placement and pointing readers to the RSPA AI Resource Hub; **Email it** opens a drafted email doing the same. Both read from the `SHARE_TARGET` constant — currently the hub URL; point it at the quiz's own URL once hosted. A–D or 1–4 keys answer questions from the keyboard.

## Optional response counter

The widget is zero-backend by default. To count completions, set the `PING_URL` constant to any endpoint (Formspree, Google Apps Script web app, etc.); each completed quiz then sends one anonymous JSON POST: `{level, track, role, ts}`. Shared-link views are never counted.

## Embedding on WordPress

The widget is one self-contained file: `ai-path-finder.html` (no dependencies beyond Google Fonts; the RSPA logo loads from gorspa.org itself, with a text fallback).

**Option A — iframe (recommended).** Upload `ai-path-finder.html` to the site (Media Library with an HTML-upload plugin, a child-theme folder, or FTP to e.g. `/ai-path-finder/index.html`), then add a Custom HTML block:

```html
<iframe id="rspa-pathfinder" src="/ai-path-finder/ai-path-finder.html"
        style="width:100%;border:0;min-height:640px" title="RSPA AI Path Finder"></iframe>
<script>
  window.addEventListener("message", function(e){
    if(e.data && e.data.type === "rspa-pathfinder-height"){
      document.getElementById("rspa-pathfinder").style.height = (e.data.height + 20) + "px";
    }
  });
</script>
```

The widget posts its height to the parent page, so the iframe auto-resizes as the receipt prints — no inner scrollbars.

**Option B — direct page.** Because the file is standalone, it can also live as its own landing page ("gorspa.org/ai-path-finder") and be linked from the hub — shared result links look cleanest this way.

**Option C — Typeform rebuild.** Recreate the six questions above with Typeform logic jumps and four endings (101/102/201/301). Trade-offs: response analytics included, but endings support only limited links/formatting and the free plan caps responses.

## Customizing

All questions, scoring, curriculum items, and links live in plain JS objects near the top of the `<script>` block (`QUESTIONS`, `LEVELS`, `ELECTIVES`). Editing a link or adding a sixth curriculum item is a one-line change. Colors are CSS variables at the top of the `<style>` block, set to RSPA brand values.
