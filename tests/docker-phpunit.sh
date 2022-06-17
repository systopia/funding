#!/bin/bash
set -eu -o pipefail

SCRIPT_DIR=$(realpath "$(dirname "$0")")
FUNDING_EXT_DIR=$(dirname "$SCRIPT_DIR")

cd "$FUNDING_EXT_DIR"
if [ ! -e tools/phpunit/vendor/bin ]; then
  "$SCRIPT_DIR/docker-prepare.sh"
fi

XDEBUG_MODE=coverage composer phpunit -- --cache-result-file=/tmp/.phpunit.result.cache "$@"
