# Security

## Reporting a problem

Please report security issues privately, not in a public issue: open the repository's **Security** tab and choose **Report a vulnerability**. You'll get a reply there.

## What the tool does with data

- **No accounts, no cookies, no backend.** The quiz is one static HTML file.
- **Answers stay in the browser.** Placement is worked out on the visitor's device. The receipt's checkmarks and the visitor's path are saved in that browser's `localStorage` and never sent anywhere.
- **Optional response counter (WordPress plugin, off by default).** If a site turns it on, each completed quiz sends one anonymous `{level, track, role, ts}` record to the address the site chooses. Nothing that identifies the visitor is included.
- **Third-party requests.** Fonts load from Google Fonts, and the RSPA logo and icon load from gorspa.org.

## The WordPress plugin

- Settings are saved only by administrators (`manage_options`), through the WordPress Settings API, and sanitized on save.
- Everything the plugin prints is escaped for its context.
- The quiz page's `host` parameter, which sends result links back to the embedding page, accepts only pages on the same site.
- The **RSPA reward** code is shown to visitors by their own browser, so anyone can read it in the page source. Use a promotional code that is fine to share, never a single-use or secret code.
