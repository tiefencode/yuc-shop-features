#!/usr/bin/env bash
# Deploys this plugin via rsync over SSH and flushes the W3 Total Cache.
# Usage: ./deploy.sh staging|production [--dry-run] [-v]
set -euo pipefail

HOST="p266187@youngandcold.de"

case "${1:-}" in
	staging)    WP_DIR="/home/www/p266187/html/wordpress-staging" ;;
	production) WP_DIR="/home/www/p266187/html/wordpress" ;;
	*) echo "Usage: $0 staging|production [--dry-run] [-v]" >&2; exit 1 ;;
esac
shift

cd "$(dirname "$0")"

find . -name '*.php' -not -path './.git/*' -print0 | xargs -0 -n1 php -l > /dev/null

rsync -rlpt --delete "$@" \
	--exclude .git \
	--exclude .DS_Store \
	--exclude .editorconfig \
	--exclude .gitignore \
	--exclude CLAUDE.md \
	--exclude README.md \
	--exclude docs \
	--exclude deploy.sh \
	./ "$HOST:$WP_DIR/wp-content/plugins/yuc-shop-features/"

if [[ " $* " != *" --dry-run "* ]]; then
	# PHP deprecation notices from plugins flood WP-CLI output; keep only the result line.
	ssh "$HOST" "wp --path=$WP_DIR w3-total-cache flush all 2>&1 | grep -E '^(Success|Error|Warning):'"
fi
