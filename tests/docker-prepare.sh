#!/bin/bash
set -eu -o pipefail

ACTIVITY_ENTITY_BRANCH=main
XCM_VERSION=1.8
IDENTITYTRACKER_VERSION=1.3
REMOTETOOLS_VERSION=0.4

FUNDING_EXT_DIR=$(dirname "$(dirname "$(realpath "$0")")")

if ! type git >/dev/null 2>&1; then
  apt -y update
  apt -y install git
fi

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
if mysql -h "$CIVICRM_DB_HOST" -P "$CIVICRM_DB_PORT" -u "$CIVICRM_DB_USER" --password="$CIVICRM_DB_PASS" "$CIVICRM_DB_NAME" -e 'SELECT 1 FROM civicrm_setting LIMIT 1;' >/dev/null 2>&1; then
  cv flush
else
  # For headless tests it is required that CIVICRM_UF is defined using the corresponding env variable.
  sed -E "s/define\('CIVICRM_UF', '([^']+)'\);/define('CIVICRM_UF', getenv('CIVICRM_UF') ?: '\1');/g" \
    -i /var/www/html/sites/default/civicrm.settings.php
  civicrm-docker-install

  # Ensure we have at least symfony/dependency-injection:~4 which is mandatory
  # for service locators. At least in Docker container with CiviCRM 5.50 there's
  # symfony/dependency-injection:~3 installed.
  cd /var/www/html/sites/all/modules/civicrm
  if composer show symfony/dependency-injection "<4" >/dev/null 2>/dev/null; then
    composer update --no-dev --no-scripts --optimize-autoloader symfony/*
  fi

  cv ext:download "activity-entity@https://github.com/systopia/activity-entity/archive/refs/heads/$ACTIVITY_ENTITY_BRANCH.zip"
  cv ext:download "de.systopia.xcm@https://github.com/systopia/de.systopia.xcm/releases/download/$XCM_VERSION/de.systopia.xcm-$XCM_VERSION.zip"
  cv ext:download "de.systopia.identitytracker@https://github.com/systopia/de.systopia.identitytracker/releases/download/$IDENTITYTRACKER_VERSION/de.systopia.identitytracker-$IDENTITYTRACKER_VERSION.zip"
  cv ext:download "de.systopia.remotetools@https://github.com/systopia/de.systopia.remotetools/archive/refs/tags/$REMOTETOOLS_VERSION.zip"
  cv ext:enable funding

  # For headless tests these files need to exist.
  touch /var/www/html/sites/all/modules/civicrm/sql/test_data.mysql
  touch /var/www/html/sites/all/modules/civicrm/sql/test_data_second_domain.mysql
fi

cd "$FUNDING_EXT_DIR"
composer update --no-progress --prefer-dist --optimize-autoloader --no-dev
composer composer-phpunit -- update --no-progress --prefer-dist
