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
- Deployment: `./deploy.sh staging|production` (rsync + W3 Total Cache flush), only when the developer asks. Staging is `https://staging.youngandcold.de` (basic auth, credentials from the developer). Test on staging first, production after the developer's go.
