#!/usr/bin/env bash
# Deploys this plugin to the live server via rsync over SSH.
# Usage: ./deploy.sh [--dry-run]
set -euo pipefail

HOST="p266187@youngandcold.de"
REMOTE_DIR="/home/www/p266187/html/wordpress/wp-content/plugins/yuc-shop-features/"

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
	./ "$HOST:$REMOTE_DIR"
