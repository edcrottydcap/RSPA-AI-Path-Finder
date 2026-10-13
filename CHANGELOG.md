# Changelog

## 1.1.0 — 2026-10-13

### WordPress plugin (new)

- `wordpress-plugin/rspa-ai-path-finder/`, downloadable as a release zip. See [docs/wordpress-plugin.md](docs/wordpress-plugin.md).
- Opens in a **popup** from a promo card, a button, or a **floating button** that can be switched on for chosen pages with no page editing (pre-filled with the seven AI Resource Hub pages).
- `[pathfinder]` shortcode (also `[PATHFINDER]`, `[rspa_ai_path_finder]`): `display="embed|card|button"`, `open="popup|page"`, plus wording options.
- **AI Path Finder** block, classic widget, `rspa_ai_path_finder()` template tag, and the quiz's own page at `/ai-path-finder/`.
- **Settings → AI Path Finder**: quiz address, share target, optional anonymous response counter, social card image, floating button, copy-ready codes, live preview.
- Card and button styled from the gorspa.org AI hub's own theme (section bar, gray panel, `.btn-link.blue`).
- Shared result links return to the page the quiz was embedded on and reopen that receipt.

### Quiz

- Curriculum refreshed against the live hub (RetailNOW 2026 and Inspire 2026 material):
  - 102: `DECK-102` → `PDF-102` 7-Day Social Content Pro Prompt.
  - 201: duplicate `POD-153` → `PDF-203` What VAR & ISV Teams Should Learn Next About AI.
  - 301: `DECK-301` and `OPS-301` → `PDF-301A` Conversational AI case studies and `PDF-301B` merchant AI by vertical.
  - New electives: `ELEC-V` Voice AI deck (merchant track at 301), `ELEC-T` AI-assisted development (technical seat), `ELEC-X` AI considerations for VAR leaders (executive seat).
- Accepts settings from `window.RSPA_PATHFINDER_CONFIG` (share target, response counter, result-link base).
- In an iframe: transparent background, and the reported height now shrinks as well as grows (it previously could not get smaller after the receipt printed).
- Esc inside a host-page popup closes it.
- Version and curriculum date are in `<meta>` tags; the receipt reads "PATH FINDER v1.1".

### Tooling

- `scripts/build-plugin.mjs` copies `index.html` into the plugin, syncs versions, and builds the zip (`--check` for CI).
- `scripts/audit-content.mjs` now also checks Google Drive links and `.docx`/`.pptx` uploads, and keeps a `REVIEWED` list of hub items deliberately left off the receipt.
- GitHub Actions: monthly content audit that opens an issue; plugin check and release-on-tag.
- `.wp-env.json` for a local WordPress test site.

## 1.0.0 — 2026-08-30

- First release: single-file quiz, GitHub Pages demo, iframe embed guide, content audit script.
