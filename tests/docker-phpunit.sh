#!/bin/bash
set -eu -o pipefail

SCRIPT_DIR=$(realpath "$(dirname "$0")")
FUNDING_EXT_DIR=$(dirname "$SCRIPT_DIR")

cd "$FUNDING_EXT_DIR"
if [ ! -e tools/phpunit/vendor/bin ]; then
  "$SCRIPT_DIR/docker-prepare.sh"
fi

export XDEBUG_MODE=coverage
# TODO: Remove retry when not needed, anymore.
# In Docker container with CiviCRM 5.51.0 the first run of phpunit fails with
# the error below. For this reason, phpunit is run a second time, when the first
# run fails.
# Uncaught Error: Class 'Civi\Api4\SearchSegment' not found in /var/www/html/sites/all/modules/civicrm/ext/search_kit/Civi/Api4/Service/Spec/Provider/SearchSegmentExtraFieldProvider.php:52
composer phpunit -- --cache-result-file=/tmp/.phpunit.result.cache "$@" || composer phpunit -- --cache-result-file=/tmp/.phpunit.result.cache "$@"
