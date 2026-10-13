#!/usr/bin/env node
/**
 * RSPA AI Path Finder — build the WordPress plugin
 *
 * index.html (repo root) is the single source of truth for the quiz. This
 * script copies it into the plugin and zips the plugin for upload:
 *
 *   wordpress-plugin/rspa-ai-path-finder/app/path-finder.html  ← index.html
 *   dist/rspa-ai-path-finder.zip                               ← the plugin folder
 *
 * Usage:
 *   node scripts/build-plugin.mjs           # copy + zip
 *   node scripts/build-plugin.mjs --check   # exit 1 if the plugin copy is stale (CI)
 *
 * Requirements: Node 18+ and the `zip` command (preinstalled on macOS and
 * GitHub's Ubuntu runners). No npm dependencies.
 */

import { readFile, writeFile, mkdir, rm } from "node:fs/promises";
import { execFileSync } from "node:child_process";
import { fileURLToPath } from "node:url";
import { dirname, join } from "node:path";

const ROOT = join(dirname(fileURLToPath(import.meta.url)), "..");
const SRC = join(ROOT, "index.html");
const PLUGIN = join(ROOT, "wordpress-plugin", "rspa-ai-path-finder");
const COPY = join(PLUGIN, "app", "path-finder.html");
const DIST = join(ROOT, "dist");
const ZIP = join(DIST, "rspa-ai-path-finder.zip");

const src = await readFile(SRC, "utf8");
const current = await readFile(COPY, "utf8").catch(() => "");

if (process.argv.includes("--check")) {
  if (src !== current) {
    console.error("Plugin copy is out of date. Run: node scripts/build-plugin.mjs");
    process.exit(1);
  }
  console.log("Plugin copy matches index.html.");
  process.exit(0);
}

// Keep the plugin's version in step with the widget's.
const version = /name="rspa-pathfinder-version" content="([^"]+)"/.exec(src)?.[1];
const main = join(PLUGIN, "rspa-ai-path-finder.php");
const block = join(PLUGIN, "blocks", "path-finder", "block.json");
const readme = join(PLUGIN, "readme.txt");
if (version) {
  const bump = async (file, pairs) => {
    let s = await readFile(file, "utf8");
    for (const [re, rep] of pairs) s = s.replace(re, rep);
    await writeFile(file, s);
  };
  await bump(main, [
    [/(\* Version:\s+)[\d.]+/, `$1${version}`],
    [/(define\( 'RSPA_APF_VERSION', ')[\d.]+(' \);)/, `$1${version}$2`],
  ]);
  await bump(block, [[/("version": ")[\d.]+(")/, `$1${version}$2`]]);
  await bump(readme, [[/(Stable tag:\s+)[\d.]+/, `$1${version}`]]);
}

await mkdir(dirname(COPY), { recursive: true });
await writeFile(COPY, src);
console.log(`Copied index.html → ${COPY.replace(ROOT + "/", "")}`);

await mkdir(DIST, { recursive: true });
await rm(ZIP, { force: true });
execFileSync("zip", ["-rqX", ZIP, "rspa-ai-path-finder", "-x", "*.DS_Store"], {
  cwd: join(ROOT, "wordpress-plugin"),
  stdio: "inherit",
});
console.log(`Built ${ZIP.replace(ROOT + "/", "")} (plugin v${version ?? "?"})`);
console.log("Upload it at WordPress → Plugins → Add Plugin → Upload Plugin.");
