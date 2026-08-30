# Placement logic

How six answers become a placement, a persona, and a curriculum. Three questions measure familiarity, one sets the track, one sets the seat, and one nudges the result — everything else is a lookup.

## The full decision flow

```mermaid
flowchart TD
    subgraph SCORE["Familiarity score (0–9)"]
        Q1["Q1 · You and AI right now<br/>never 0 · dabbled 1 · daily 2 · building 3"]
        Q2["Q2 · Vocabulary check: RAG<br/>cloths? 0 · heard it 1 · can explain 2 · implemented 3"]
        Q5["Q5 · Company pulse<br/>none 0 · individuals 1 · approved tools 2 · in products 3"]
        SUM["Sum: 0–9"]
        Q1 --> SUM
        Q2 --> SUM
        Q5 --> SUM
    end

    Q6{"Q6 · What's holding you back?"}
    SUM --> Q6
    Q6 -- "don't know where to start · −1" --> ADJ["Adjusted score<br/>(clamped 0–9)"]
    Q6 -- "security / IP worries · ±0<br/>(adds guardrails elective)" --> ADJ
    Q6 -- "no time · ±0" --> ADJ
    Q6 -- "hand me the advanced stuff · +1" --> ADJ

    ADJ -- "0–2" --> L101["AI 101 · The Explorer<br/>build the foundation"]
    ADJ -- "3–5" --> L102["AI 102 · The Practitioner<br/>level up prompting"]
    ADJ -- "6–7" --> L201["AI 201 · The Operator<br/>deploy across the business"]
    ADJ -- "8–9" --> L301["AI 301 · The Trailblazer<br/>merchant-facing & strategic"]
```

## Personalization on top of the level

```mermaid
flowchart TD
    Q4{"Q4 · The goal → track"}
    Q4 -- "understand AI" --> TF["Foundations (f)"]
    Q4 -- "better results from tools" --> TP["Practitioner (p)"]
    Q4 -- "run company efficiently" --> TI["Internal efficiency (i)"]
    Q4 -- "bring AI to merchants" --> TM["Merchant-facing (m)"]

    Q3{"Q3 · Your seat → role"}
    Q3 --> RE["Exec"] & RS["Sales"] & RO["Support/Ops"] & RT["Technical"]

    TI -- "at 101/102" --> EI["Elective: Internal AI preview"]
    TM -- "below 301" --> EM["Elective: Merchant-facing preview"]
    RISK["Q6 = security worries"] --> ES["Elective: IP & accuracy guardrails"]

    RE & RS & RO & RT --> R201["201's lead item description becomes<br/>that seat's team playbook"]
    TF & TP & TI & TM --> CODE["Deep-link code: level + track<br/>e.g. ?r=201i"]
```

## The curriculum lookup

Each level maps to five fixed items (see [flow-spec.md](flow-spec.md) for every title, description, and URL):

| Level | Persona | Curriculum spine |
|---|---|---|
| **AI 101** | The Explorer | Terminology → Misconceptions → Basic prompting → Podcast ep. 151 → "one tool, one task" action |
| **AI 102** | The Practitioner | Cheat sheet & few-shot → C.R.A.F.T. & personas → Protect your IP → Inspire decks → Podcast ep. 153 |
| **AI 201** | The Operator | Internal AI by team (role-personalized) → Top 10 Use Cases PDF → Exec application PDF → Governance guardrails → Podcast ep. 153 |
| **AI 301** | The Trailblazer | Merchant-facing AI by vertical → Podcast ep. 159 (AI & payments) → Voice AI / Predictive decks → Pipeline intelligence → Join the AI Advisory Group |

Electives are appended after the spine and deduped against it, so a receipt never lists the same resource twice.

## Why these three questions score and the others don't

Q1 (self-report), Q2 (a concrete vocabulary probe), and Q5 (organizational maturity) triangulate *familiarity* from three different angles — personal claim, verifiable knowledge, and environment — which keeps any one inflated answer from overplacing someone. Q4 and Q3 are preferences, not abilities, so they steer *what* the curriculum contains rather than *how advanced* it is. Q6 is the honesty valve: "I don't know where to start" pulls a borderline score down into a gentler placement, and "hand me the advanced stuff" grants the benefit of the doubt upward — each by exactly one point, so self-perception tunes but never overrides the measured score.
