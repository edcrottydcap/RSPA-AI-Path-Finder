#!/usr/bin/env node
/**
 * RSPA AI Path Finder — content audit
 *
 * Compares the curriculum links inside index.html against the live RSPA
 * AI Resource Hub, so the widget can be kept current over time.
 *
 * What it does:
 *   1. Extracts every gorspa.org URL referenced by the widget.
 *   2. Checks each one still resolves (catches removed/renamed pages).
 *   3. Crawls the hub's section pages and collects their content links.
 *   4. Reports hub links the widget doesn't use yet — new-content candidates.
 *
 * Usage:
 *   node scripts/audit-content.mjs                # audits ../index.html
 *   node scripts/audit-content.mjs path/to/file   # audit another copy
 *
 * Requirements: Node 18+ (built-in fetch). No dependencies. Run it from a
 * machine with normal internet access. Exit code 1 if any widget link is broken.
 */

import { readFile } from "node:fs/promises";
import { fileURLToPath } from "node:url";
import { dirname, join } from "node:path";

const HERE = dirname(fileURLToPath(import.meta.url));
const WIDGET = process.argv[2] || join(HERE, "..", "index.html");

const HUB_PAGES = [
  "https://www.gorspa.org/ai-resource-hub/",
  "https://www.gorspa.org/ai-terminology/",
  "https://www.gorspa.org/ai-tools/",
  "https://www.gorspa.org/ai-internal-applications/",
  "https://www.gorspa.org/ai-merchant-facing-applications/",
  "https://www.gorspa.org/ai-misconceptions-best-practices/",
  "https://www.gorspa.org/rspa-ai-education-content/",
];

const UA = { "User-Agent": "RSPA-AI-Path-Finder content audit (github.com/edcrottydcap/RSPA-AI-Path-Finder)" };

function normalize(u) {
  try {
    const url = new URL(u);
    url.hash = ""; url.search = "";
    let s = url.toString();
    if (!s.endsWith(".pdf") && !s.endsWith("/")) s += "/";
    return s.replace("://gorspa.org", "://www.gorspa.org");
  } catch { return null; }
}

async function status(url) {
  for (const method of ["HEAD", "GET"]) {
    try {
      const r = await fetch(url, { method, redirect: "follow", headers: UA });
      if (r.ok) return r.status;
      if (method === "GET") return r.status;
    } catch (e) {
      if (method === "GET") return "unreachable (" + (e.cause?.code || e.message) + ")";
    }
  }
}

// 1. URLs the widget uses ----------------------------------------------------
const html = await readFile(WIDGET, "utf8");
const used = new Set();
// absolute references
for (const m of html.matchAll(/https:\/\/(?:www\.)?gorspa\.org\/[^\s"'<>\\]+/g)) {
  const n = normalize(m[0]);
  if (n) used.add(n);
}
// HUB+"..." concatenations in the script
for (const m of html.matchAll(/HUB\s*\+\s*"([^"]+)"/g)) {
  const n = normalize("https://www.gorspa.org/" + m[1]);
  if (n) used.add(n);
}
used.delete(normalize("https://www.gorspa.org/wp-content/uploads/logo-color.svg")); // asset, not curriculum

console.log(`# Content audit — ${new Date().toISOString().slice(0, 10)}\n`);
console.log(`Widget file: ${WIDGET}`);
console.log(`Curriculum/link references found: ${used.size}\n`);

// 2. Check the widget's links still resolve ----------------------------------
console.log("## Link health\n");
let broken = 0;
for (const u of [...used].sort()) {
  const s = await status(u);
  const ok = s === 200;
  if (!ok) broken++;
  console.log(`${ok ? "  OK " : "FAIL "} ${s}  ${u}`);
}

// 3. Crawl the hub for content links -----------------------------------------
console.log("\n## New on the hub (not yet in the widget)\n");
const seen = new Set();
let crawlFailures = 0;
for (const page of HUB_PAGES) {
  let body;
  try {
    const r = await fetch(page, { headers: UA });
    if (!r.ok) { console.log(`(could not read ${page}: HTTP ${r.status})`); crawlFailures++; continue; }
    body = await r.text();
  } catch (e) { console.log(`(could not read ${page}: ${e.cause?.code || e.message})`); crawlFailures++; continue; }
  for (const m of body.matchAll(/href="(https?:\/\/(?:www\.)?gorspa\.org\/[^"]+|\/[^"]+)"/g)) {
    const raw = m[1].startsWith("/") ? "https://www.gorspa.org" + m[1] : m[1];
    const n = normalize(raw);
    if (!n || seen.has(n) || used.has(n)) continue;
    // keep only likely content: hub sections, blog posts, uploaded PDFs
    const isContent = /gorspa\.org\/(blog-[^/]+\/|ai-[^/]+\/|rspa-ai-[^/]+\/|wp-content\/uploads\/[^/]+\.pdf)$/.test(n);
    if (!isContent) continue;
    seen.add(n);
    console.log(`NEW  ${n}   (linked from ${page.replace("https://www.gorspa.org", "")})`);
  }
}
if (seen.size === 0 && crawlFailures === 0) console.log("(nothing new — the widget covers all current hub content links)");

// 4. Summary -------------------------------------------------------------------
console.log(`\n## Summary\n`);
console.log(`- Widget links checked: ${used.size}, broken: ${broken}`);
console.log(`- New hub content candidates: ${seen.size}`);
console.log(`- Hub pages unreadable: ${crawlFailures}`);
console.log(`\nNext step: for each NEW link worth including, add an item to LEVELS or`);
console.log(`ELECTIVES in index.html (see docs/customization.md), or hand this report`);
console.log(`to an AI assistant with the maintainer prompt in docs/content-maintenance.md.`);
process.exit(broken > 0 ? 1 : 0);
