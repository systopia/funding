#!/bin/bash
set -eu -o pipefail

FUNDING_EXT_DIR=$(dirname "$(dirname "$0")")

i=0
while ! mysql -h "$CIVICRM_DB_HOST" -P "$CIVICRM_DB_PORT" -u "$CIVICRM_DB_USER" --password="$CIVICRM_DB_PASS" -e 'SELECT 1;' >/dev/null 2>&1; do
  i=$((i+1))
  if [ $i -gt 10 ]; then
    echo "Failed to connect to database" >&2
    exit 1
  fi

  echo -n .
  sleep 1
done

echo

cv flush >/dev/null 2>/dev/null || {
  civicrm-docker-install
  cv ext:enable funding
}

cd "$FUNDING_EXT_DIR"
composer update --prefer-dist --no-dev
composer composer-phpunit -- update --prefer-dist
