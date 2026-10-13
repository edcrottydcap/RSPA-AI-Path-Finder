# Embedding without the plugin

**On WordPress, use the plugin.** It does everything below automatically, adds the popup, card, and floating button, and keeps settings in the dashboard: [wordpress-plugin.md](wordpress-plugin.md).

This page is for sites that can't install plugins, or for hosting the quiz somewhere other than WordPress. The quiz is one self-contained file, `index.html`, with no backend.

## Option A: iframe on any page

**1. Host the file.** Upload `index.html` somewhere public, for example `/ai-path-finder/index.html` in the web root via FTP or the host's file manager, or use the GitHub Pages copy (Option C).

**2. Add this to the page** (WordPress: a *Custom HTML* block). Adjust `src`:

```html
<iframe data-rspa-pathfinder src="/ai-path-finder/"
        style="width:100%;border:0;height:640px"
        allow="clipboard-write" title="RSPA AI Path Finder"></iframe>
<script>
  window.addEventListener("message", function (e) {
    if (!e.data || e.data.type !== "rspa-pathfinder-height") return;
    document.querySelectorAll("iframe[data-rspa-pathfinder]").forEach(function (f) {
      if (f.contentWindow === e.source) f.style.height = (e.data.height + 4) + "px";
    });
  });
</script>
```

The quiz posts its height whenever its content changes, so the frame grows as the receipt prints and shrinks again on Retake, with no inner scroll bars. Inside a frame the quiz drops its gray background so it sits on the host page. `allow="clipboard-write"` lets "Copy my result link" work.

## Option B: its own page

Because the file is standalone, it can *be* the page (`https://www.example.org/ai-path-finder/`). It's the best setup for sharing, because deep links (`?r=201i`) and social cards point at a clean URL. Update two things in the file:

- the `og:url` meta tag in the `<head>`: set it to the final URL;
- the `SHARE_TARGET` constant in the script: point LinkedIn and email shares at the quiz itself instead of the hub.

## Option C: GitHub Pages (zero server changes)

This repo already serves the quiz at **https://edcrottydcap.github.io/RSPA-AI-Path-Finder/** (Settings → Pages → Deploy from branch `main` / root). It redeploys automatically on every push to `main`. That URL can be linked directly or used as the iframe `src` in Option A. For production, hosting on gorspa.org itself keeps the logo, links, and analytics first-party, which is what the plugin does.

## Settings without the plugin

The plugin sets these through `window.RSPA_PATHFINDER_CONFIG`. A hand-hosted copy can define the same object in a `<script>` placed before the quiz's script, or edit the defaults in the file:

```html
<script>
  window.RSPA_PATHFINDER_CONFIG = {
    shareTarget: "https://www.example.org/ai-path-finder/", // LinkedIn/email shares
    pingUrl: "",                                         // optional response counter
    resultBase: "https://www.example.org/ai-path-finder/"  // "Copy my result link" base
  };
</script>
```

## Notes

- The RSPA logo loads from `gorspa.org/wp-content/uploads/logo-color.svg`. If it can't load, a typographic `R·S·P·A` fallback appears instead of a broken image.
- Fonts come from Google Fonts (Mulish and Spline Sans Mono); Proxima Nova is used first where the host page provides it.
- The quiz stores nothing: no cookies, no localStorage, no tracking.
