#!/bin/bash
set -eu -o pipefail

SCRIPT_DIR=$(realpath "$(dirname "$0")")
FUNDING_EXT_DIR=$(dirname "$SCRIPT_DIR")

cd "$FUNDING_EXT_DIR"
if [ ! -e tools/phpunit/vendor/bin ]; then
  "$SCRIPT_DIR/docker-prepare.sh"
fi

export XDEBUG_MODE=coverage
# TODO: Remove when not needed, anymore.
# In Docker container with CiviCRM 5.5? all deprecations are reported as direct
# deprecations so "disabling" check of deprecation count is necessary for the
# tests to pass (if baselineFile does not contain all deprecations).
export SYMFONY_DEPRECATIONS_HELPER="max[total]=99999&baselineFile=./tests/ignored-deprecations.json"

composer run --timeout=600 phpunit --cache-result-file=/tmp/.phpunit.result.cache "$@"
