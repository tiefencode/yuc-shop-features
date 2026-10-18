# yuc-shop-features

WooCommerce shop features for [Young & Cold Records](https://youngandcold.de), implemented as a plugin so they are independent of the active theme (Twenty Twenty-Five, managed with the Site Editor).

Features and implementation notes: [docs/spec.md](docs/spec.md).

## Requirements

- WordPress 6.5+
- WooCommerce 11+
- PHP 8.1+
- Pods (release data for the audio player)
- Contact Form 7 (out-of-stock contact form)

## Structure

```
yuc-shop-features.php   # plugin bootstrap
includes/               # one file per feature
templates/              # PHP templates rendered by the plugin
assets/                 # plain JavaScript and CSS, no jQuery
docs/                   # spec
```

## Deployment

No build step. The repository root is the plugin directory; `deploy.sh` lints all PHP files, syncs them to `wp-content/plugins/yuc-shop-features/` via rsync over SSH and flushes the W3 Total Cache with WP-CLI. Host and paths are set at the top of the script.

```bash
./deploy.sh staging --dry-run   # show what would change on staging
./deploy.sh staging             # deploy to https://staging.youngandcold.de
./deploy.sh production          # deploy to https://youngandcold.de
```
