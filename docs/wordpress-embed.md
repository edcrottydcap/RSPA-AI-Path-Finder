# Embedding on WordPress (gorspa.org)

The widget is one self-contained file — `index.html` — with no dependencies on the WordPress theme, no plugins required by the widget itself, and no backend. There are three ways to put it on the site, in order of recommendation.

## Option A — iframe on any page (recommended)

**1. Upload the file to the site.** Any of these works:

- **FTP / hosting file manager:** upload `index.html` to a folder in the web root, e.g. `/ai-path-finder/index.html` → it's now served at `https://www.gorspa.org/ai-path-finder/`.
- **Media Library:** WordPress blocks `.html` uploads by default; a small plugin such as "File Manager" or an HTML-upload enabler lifts that. FTP is cleaner if available.
- **Child theme folder:** drop it in the theme directory and note the URL.

**2. Add a Custom HTML block** to the AI Resource Hub page (or any page) with this snippet, adjusting the `src` to wherever the file landed:

```html
<iframe id="rspa-pathfinder" src="/ai-path-finder/"
        style="width:100%;border:0;min-height:640px"
        title="RSPA AI Path Finder"></iframe>
<script>
  window.addEventListener("message", function (e) {
    if (e.data && e.data.type === "rspa-pathfinder-height") {
      document.getElementById("rspa-pathfinder").style.height =
        (e.data.height + 20) + "px";
    }
  });
</script>
```

That's the whole integration. The widget posts its own height to the page whenever its content changes, so the iframe grows as the receipt prints — no scrollbars, no cut-off receipt. The `min-height` just prevents a flash before the first message arrives.

## Option B — its own page

Because the file is standalone, it can simply *be* a page: `https://www.gorspa.org/ai-path-finder/`. Link to it from the hub with a button ("Find your AI path →"). This is the best setup for sharing, because deep links (`?r=201i`) and social cards point at a clean URL. If you use this option, update two things inside the file:

- the `og:url` meta tag in the `<head>` — set it to the final URL;
- the `SHARE_TARGET` constant in the script — point LinkedIn/email shares at the quiz itself instead of the hub.

## Option C — GitHub Pages (zero server changes)

In this repo: **Settings → Pages → Source: Deploy from a branch → `main` / root**. Within a minute the quiz is live at `https://<owner>.github.io/RSPA-AI-Path-Finder/`. That URL can be linked directly or used as the iframe `src` in Option A — useful for demos and for letting RSPA evaluate it before touching their hosting. (For production, hosting on gorspa.org itself keeps the logo, links, and analytics first-party.)

## After it's live — a short checklist

- [ ] Update `og:url` in `index.html` to the real URL (social cards).
- [ ] Optionally swap `og:image` for a 1200×630 PNG card for richer LinkedIn/Facebook unfurls.
- [ ] Point `SHARE_TARGET` at the live quiz URL so shares drive people straight into it.
- [ ] Optionally set `PING_URL` to count completions — see [customization.md](customization.md).
- [ ] Take the quiz once on desktop and once on a phone from the live page.

## Notes

- The RSPA logo is loaded from `gorspa.org/wp-content/uploads/logo-color.svg`. On gorspa.org that's a same-site request; anywhere else it still works as a normal cross-site image. If it ever can't load, the widget shows a typographic `R·S·P·A` fallback instead of a broken image.
- Fonts come from Google Fonts (Mulish + Spline Sans Mono). On gorspa.org the widget's first-choice face, Proxima Nova, resolves from the site itself inside Option B-style hosting; inside an iframe the Mulish fallback is what renders, which was chosen to sit comfortably next to Proxima Nova.
- The widget stores nothing — no cookies, no localStorage, no tracking. GDPR-quiet by default.
