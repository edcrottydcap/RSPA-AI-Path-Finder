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

// Hub links already reviewed and deliberately left off the receipt. They are
// counted, not re-reported, so each month's "NEW" list is only truly new
// content. Remove an entry to have the audit raise it again. Keys are
// normalized gorspa.org URLs or "drive:<file id>".
const REVIEWED = {
  "https://www.gorspa.org/wp-content/uploads/RSPA-White-Glove-Service-Flyer.pdf": "membership flyer in the site footer, not AI content",
  "https://www.gorspa.org/rspa-ai-education-content/": "section page; its decks and PDFs are linked directly instead",
  "https://www.gorspa.org/wp-content/uploads/ShowSync.pdf": "vendor promo with leftover AI-citation text",
  "https://www.gorspa.org/wp-content/uploads/Restaurants-and-AI-Tools.pdf": "restaurant-only; candidate if the quiz ever asks for a vertical",
  "https://www.gorspa.org/wp-content/uploads/AI-Transforming-Customer-Service.pdf": "candidate for 301; PDF-301A/B chosen (2026-10)",
  "https://www.gorspa.org/wp-content/uploads/AI-the-Future-of-Payments-How-VARs-and-ISVs-Can-Stay-Ahead-of-the-Changing-Landscape.docx": "Word file overlapping POD-159; candidate for a payments track",
  "https://www.gorspa.org/wp-content/uploads/Built-to-Bend-An-AI-Maturity-Model-for-VAR-ISV-Growth.docx": "Word file; ELEC-X chosen for execs (2026-10)",
  "drive:1attVd02G7IzdZ6zG1OGIayihEH8IdDYS": "Inspire 2026 AI Introduction deck — 46 MB",
  "drive:14x_rCB6Av26_A1AiTj9GZJFEU3jwEwS9": "Inspire 2026 AI Tools Overview — tool catalog",
  "drive:1gUU5ROcRuf8AbI-heyP9IZ2p-YODrsC3": "Inspire 2026 AI Productivity Tools — 130 MB, Drive virus-scan page",
  "drive:1WvUrE-xGM_Kg3YLclHxdNYdf4PYlv52t": "Inspire 2026 vendor demo (Rolo)",
  "drive:1YZbk33hOUbu7jL8wZ7hVUhOUIWr8pZLP": "Inspire 2026 vendor demo (AI Business Toolkit)",
  "drive:1vgvrOulHu-EzfbqWjy2-m6LZ1G1m6TzD": "Inspire 2026 vendor demo (LeadEase)",
  "drive:1ZcHZzD7MndVt2KbG3JTzvwXcHGLy0QwK": "Inspire 2026 AI Predictive Analysis — candidate 301 elective",
  "drive:1OzGo-_5EegC9txhLgyKs2KRDtEspt5dc": "Inspire 2026 download-all zip",
};
let reviewedSeen = 0;

const UA = { "User-Agent": "RSPA-AI-Path-Finder content audit (github.com/edcrottydcap/RSPA-AI-Path-Finder)" };

function normalize(u) {
  try {
    const url = new URL(u);
    url.hash = ""; url.search = "";
    let s = url.toString();
    if (!/\.(pdf|docx?|pptx?)$/.test(s) && !s.endsWith("/")) s += "/";
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
// Google Drive downloads (Inspire decks are hosted there and linked from the hub)
const drive = new Set();
for (const m of html.matchAll(/https:\/\/drive\.google\.com\/[^\s"'<>\\]+/g)) drive.add(m[0].replace(/&amp;/g, "&"));

console.log(`# Content audit — ${new Date().toISOString().slice(0, 10)}\n`);
console.log(`Widget file: ${WIDGET}`);
console.log(`Curriculum/link references found: ${used.size} on gorspa.org, ${drive.size} on Google Drive\n`);

// 2. Check the widget's links still resolve ----------------------------------
console.log("## Link health\n");
let broken = 0;
for (const u of [...used].sort()) {
  const s = await status(u);
  const ok = s === 200;
  if (!ok) broken++;
  console.log(`${ok ? "  OK " : "FAIL "} ${s}  ${u}`);
}
for (const u of [...drive].sort()) {
  const s = await status(u);
  const ok = s === 200;
  if (!ok) broken++;
  console.log(`${ok ? "  OK " : "FAIL "} ${s}  ${u}`);
}
// Drive ids the widget uses, so the crawl below doesn't re-report them
const usedDriveIds = new Set([...drive].map((u) => /id=([\w-]+)/.exec(u)?.[1]).filter(Boolean));

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
  for (const m of body.matchAll(/href="https:\/\/drive\.google\.com\/[^"]*?id=([\w-]+)[^"]*"/g)) {
    const key = "drive:" + m[1];
    if (usedDriveIds.has(m[1]) || seen.has(key)) continue;
    seen.add(key);
    if (REVIEWED[key]) { reviewedSeen++; continue; }
    console.log(`NEW  https://drive.google.com/uc?export=download&id=${m[1]}   (Drive file linked from ${page.replace("https://www.gorspa.org", "")})`);
  }
  for (const m of body.matchAll(/href="(https?:\/\/(?:www\.)?gorspa\.org\/[^"]+|\/[^"]+)"/g)) {
    const raw = m[1].startsWith("/") ? "https://www.gorspa.org" + m[1] : m[1];
    const n = normalize(raw);
    if (!n || seen.has(n) || used.has(n)) continue;
    if (REVIEWED[n]) { seen.add(n); reviewedSeen++; continue; }
    // keep only likely content: hub sections, blog posts, uploaded PDFs
    const isContent = /gorspa\.org\/(blog-[^/]+\/|ai-[^/]+\/|rspa-ai-[^/]+\/|wp-content\/uploads\/[^/]+\.(?:pdf|docx?|pptx?))$/.test(n);
    if (!isContent) continue;
    seen.add(n);
    console.log(`NEW  ${n}   (linked from ${page.replace("https://www.gorspa.org", "")})`);
  }
}
const fresh = seen.size - reviewedSeen;
if (fresh === 0 && crawlFailures === 0) console.log("(nothing new — every hub content link is on a receipt or in REVIEWED)");

// 4. Summary -------------------------------------------------------------------
console.log(`\n## Summary\n`);
console.log(`- Widget links checked: ${used.size}, broken: ${broken}`);
console.log(`- New hub content candidates: ${fresh}`);
console.log(`- Reviewed & deliberately unused (see REVIEWED in this script): ${reviewedSeen}`);
console.log(`- Hub pages unreadable: ${crawlFailures}`);
console.log(`\nNext step: for each NEW link worth including, add an item to LEVELS or`);
console.log(`ELECTIVES in index.html (see docs/customization.md), or hand this report`);
console.log(`to an AI assistant with the maintainer prompt in docs/content-maintenance.md.`);
process.exit(broken > 0 ? 1 : 0);
