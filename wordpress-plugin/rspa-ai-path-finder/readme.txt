=== RSPA AI Path Finder ===
Contributors: rspa
Tags: quiz, ai, education, block, shortcode
Requires at least: 6.1
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Six quick questions, one personalized AI curriculum from the RSPA AI Resource Hub — printed as a shareable point-of-sale receipt with a checklist that tracks what you've read.

== Description ==

The AI Path Finder places visitors at AI 101, 102, 201, or 301 and prints a five-item reading path drawn from the RSPA AI Resource Hub. No signup, no cookies, no tracking.

Put it anywhere — by default the quiz opens in a popup, so it never has to fit your layout:

* **Floating button** — tick one box in Settings → AI Path Finder and a "Find your AI path" button appears on the pages you list (pre-filled with the AI Resource Hub pages), in the corner you choose. No page editing; works with any template. After the quiz it becomes the visitor's progress tracker: "AI 201 path · 2 of 7", a Next-up link, the checklist, and encouragement. Visiting a page on the path ticks it off.
* **Shortcode** — `[pathfinder display="card"]` (a promo card), `[pathfinder display="button"]`, or `[pathfinder]` (the whole quiz inline). Works in the classic editor, Elementor, Divi, WPBakery, and text widgets.
* **Block** — search for "AI Path Finder" in the block inserter; choose card, button, or embedded quiz.
* **Its own page** — the quiz is served at `/ai-path-finder/` the moment the plugin is activated (address configurable).
* **Widget** — "RSPA AI Path Finder" under Appearance → Widgets for classic themes.
* **Template tag** — `rspa_ai_path_finder( array( 'display' => 'card' ) )` for custom page templates.

Settings live under **Settings → AI Path Finder**, alongside copy-ready embed codes and a live preview.

The quiz runs inside an auto-sizing iframe, so your theme's CSS can't break it and it can't affect your theme.

Full guide: https://github.com/edcrottydcap/RSPA-AI-Path-Finder/blob/main/docs/wordpress-plugin.md

== Installation ==

1. Plugins → Add New → Upload Plugin → choose `rspa-ai-path-finder.zip` → Install Now → Activate.
2. Visit `yoursite.org/ai-path-finder/` to see the quiz on its own page.
3. Edit any page, add the "AI Path Finder" block, and publish.

== Frequently Asked Questions ==

= The /ai-path-finder/ address shows "Page not found". =
Go to Settings → Permalinks and click Save (no changes needed). This refreshes WordPress's address rules.

= Can I change the address? =
Yes: Settings → AI Path Finder → Quiz address.

= What shortcode options are there? =
`display="embed|card|button"`, `open="popup|page"`, `text="Button label"`, `heading="…"`, `blurb="…"` (card wording), `new_tab="yes"`, `height="640"` (starting height before the inline quiz sizes itself), `class="my-class"`. `[pathfinder]`, `[PATHFINDER]` and `[rspa_ai_path_finder]` are interchangeable.

= I pasted the shortcode but nothing shows. =
Some custom page templates don't print the page content. Use the floating button instead, or have a developer add `<?php if ( function_exists( 'rspa_ai_path_finder' ) ) { rspa_ai_path_finder( array( 'display' => 'card' ) ); } ?>` to the template.

= My change doesn't appear on the live site. =
Clear the host's page cache (Flywheel, Kinsta, WP Engine, or a caching plugin) and reload.

= What happens with shared result links? =
A visitor's "Copy my result link" points back at the page the quiz is embedded on, with `?r=201i` appended. Anyone opening it sees that receipt and a prompt to take the quiz themselves.

= Does it collect data? =
No. The receipt's checkmarks (which resources a visitor has opened) are saved in that visitor's own browser storage and never sent anywhere. Optionally, Settings → AI Path Finder → Response counter sends one anonymous `{level, track, role, ts}` POST per completed quiz to an endpoint you choose.

= How is the curriculum updated? =
The curriculum lives in the quiz file. A new plugin version is released from the GitHub repository when the RSPA AI Resource Hub changes; upload it over the old one (Plugins → Add New → Upload → Replace current with uploaded).

== Changelog ==

= 1.2.0 =
* Receipt lines get a checkbox that ticks when the visitor opens that resource, with a "picked up" count, remembered in the visitor's own browser.
* Each line shows the resource's value; a coupon line (Settings → AI Path Finder → Coupon code, default PATHFINDER) takes 100% off.

= 1.1.0 =
* First release as a WordPress plugin: popup card/button, floating button, block, [pathfinder] shortcode, standalone page, classic widget, template tag, settings screen.
* Card and button styled to match the gorspa.org AI Resource Hub.
* Floating button and popup stay on screen even when a theme's layout overflows on phones.
* Curriculum refreshed against the RSPA AI Resource Hub.
* Embedded quiz now shrinks as well as grows, and sits transparently on the host page.
