# yuc-shop-features

WooCommerce plugin for youngandcold.de. Repository root = plugin directory. What to build and why: `docs/spec.md` (read it first).

## Context

- Live site runs Twenty Twenty-Five (block theme) with WooCommerce 11.2.1, Pods, Germanized, Contact Form 7. Cart and checkout are shortcodes.
- The old Storefront child theme with the original code lives outside this repo at `/Users/moswald/Projekte/youngandcold-wp/youngandcold/`. Paths in the spec (`functions.php:221`) refer to it. Port from there, do not modify it.
- No local WordPress, no Docker. `php -l` is available (Homebrew PHP 8.5); run it on every PHP file after editing. Functional testing happens on the live server after deployment by the developer.

## Conventions

- The old theme code is the reference for *behaviour*, not for *implementation*. It is often sloppy; do not copy its workarounds. "Better" means fewer moving parts and current WordPress/WooCommerce APIs, never more code, options, or structure. A feature that is already a few plain lines stays a few plain lines.
- Procedural PHP, prefix `yuc_`, no classes, no abstractions beyond what the spec lists.
- One file per feature in `includes/`; the bootstrap loads `includes/*.php` automatically.
- Plain JavaScript only, no jQuery. Enqueue assets only on the pages that need them.
- Escape output (`esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`).
- Commits: Conventional Commits, English. Before committing run `php -l` on every PHP file and `node --check` on every JS file, read `git diff --staged`, stage files explicitly (no `git add -A`), one logical change per commit, spec updated in the same commit when behaviour changes.
- Branches: one feature branch per spec feature (`feat/<name>`); `main` is what runs in production. Merge into `main` only after the staging test passed.
- Deployment: `./deploy.sh staging|production` (rsync + W3 Total Cache flush), only when the developer asks. Feature branches go to staging (`https://staging.youngandcold.de`, basic auth via `$STAGING_AUTH` from `.claude/settings.local.json`); `main` goes to production after the developer's go.
- After deploying to staging, run the `staging-check` subagent for that one feature (pass its number, e.g. "staging-check for feature 9"); it verifies the feature file's "Test on staging" cases from the page source and reports what remains to test manually. Do not test other features unless the developer asks for a regression check.
- Status `live` is set only after `./deploy.sh production` ran; merging alone does not make a feature live.
