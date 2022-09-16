#!/bin/bash
set -eu -o pipefail

XCM_VERSION=1.8
IDENTITYTRACKER_VERSION=1.3
REMOTETOOLS_VERSION=0.4

FUNDING_EXT_DIR=$(dirname "$(dirname "$(realpath "$0")")")

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

export XDEBUG_MODE=off
cv flush >/dev/null 2>/dev/null || {
  # For headless tests it is required that CIVICRM_UF is defined using the corresponding env variable.
  sed -E "s/define\('CIVICRM_UF', '([^']+)'\);/define('CIVICRM_UF', getenv('CIVICRM_UF') ?: '\1');/g" \
    -i /var/www/html/sites/default/civicrm.settings.php
  civicrm-docker-install

  cv ext:download "de.systopia.xcm@https://github.com/systopia/de.systopia.xcm/releases/download/$XCM_VERSION/de.systopia.xcm-$XCM_VERSION.zip"
  cv ext:download "de.systopia.identitytracker@https://github.com/systopia/de.systopia.identitytracker/releases/download/$IDENTITYTRACKER_VERSION/de.systopia.identitytracker-$IDENTITYTRACKER_VERSION.zip"
  cv ext:download "de.systopia.remotetools@https://github.com/systopia/de.systopia.remotetools/archive/refs/tags/$REMOTETOOLS_VERSION.zip"
  cv ext:enable funding

  # For headless tests these files need to exist.
  touch /var/www/html/sites/all/modules/civicrm/sql/test_data.mysql
  touch /var/www/html/sites/all/modules/civicrm/sql/test_data_second_domain.mysql
}

cd "$FUNDING_EXT_DIR"
composer update --no-progress --prefer-dist --optimize-autoloader --no-dev
composer composer-phpunit -- update --no-progress --prefer-dist
