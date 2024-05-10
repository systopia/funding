#!/bin/bash
set -eu -o pipefail

ACTIVITY_ENTITY_BRANCH=main
EXTERNAL_FILE_BRANCH=main
CIVIBANKING_VERSION=0.8.3
#CIVIOFFICE_VERSION=1.0-beta1
CIVIOFFICE_BRANCH=master
XCM_VERSION=1.12.0
#IDENTITYTRACKER_VERSION=1.3
IDENTITYTRACKER_BRANCH=master
#REMOTETOOLS_VERSION=0.4
REMOTETOOLS_BRANCH=master

EXT_DIR=$(dirname "$(dirname "$(realpath "$0")")")
EXT_NAME=$(basename "$EXT_DIR")

if ! type git >/dev/null 2>&1; then
  apt -y update
  apt -y install git unoconv
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

  # Avoid this error:
  # The autoloader expected class "Civi\ActionSchedule\Mapping" to be defined in
  # file "[...]/Civi/ActionSchedule/Mapping.php". The file was found but the
  # class was not in it, the class name or namespace probably has a typo.
  rm -f /var/www/html/sites/all/modules/civicrm/Civi/ActionSchedule/Mapping.php

  # For headless tests these files need to exist.
  touch /var/www/html/sites/all/modules/civicrm/sql/test_data.mysql
  touch /var/www/html/sites/all/modules/civicrm/sql/test_data_second_domain.mysql

  cv ext:download "activity-entity@https://github.com/systopia/activity-entity/archive/refs/heads/$ACTIVITY_ENTITY_BRANCH.zip"
  cv ext:download "external-file@https://github.com/systopia/external-file/archive/refs/heads/$EXTERNAL_FILE_BRANCH.zip"
  cv ext:download "org.project60.banking@https://github.com/Project60/org.project60.banking/releases/download/$CIVIBANKING_VERSION/org.project60.banking-$CIVIBANKING_VERSION.zip"
  # For some reason fails with this error: Class "CRM_Civioffice_Upgrader" not found.
  # If ext:enable is called afterwards, everything is ok.
  #cv ext:download "de.systopia.civioffice@https://github.com/systopia/de.systopia.civioffice/archive/refs/tags/$CIVIOFFICE_VERSION.zip" ||:
  cv ext:download "de.systopia.civioffice@https://github.com/systopia/de.systopia.civioffice/archive/refs/heads/$CIVIOFFICE_BRANCH.zip" ||:
  cv ext:enable de.systopia.civioffice
  cv ext:download "de.systopia.xcm@https://github.com/systopia/de.systopia.xcm/releases/download/$XCM_VERSION/de.systopia.xcm-$XCM_VERSION.zip"
  #cv ext:download "de.systopia.identitytracker@https://github.com/systopia/de.systopia.identitytracker/releases/download/$IDENTITYTRACKER_VERSION/de.systopia.identitytracker-$IDENTITYTRACKER_VERSION.zip"
  cv ext:download "de.systopia.identitytracker@https://github.com/systopia/de.systopia.identitytracker/archive/refs/heads/$IDENTITYTRACKER_BRANCH.zip"
  #cv ext:download "de.systopia.remotetools@https://github.com/systopia/de.systopia.remotetools/archive/refs/tags/$REMOTETOOLS_VERSION.zip"
  cv ext:download "de.systopia.remotetools@https://github.com/systopia/de.systopia.remotetools/archive/refs/heads/$REMOTETOOLS_BRANCH.zip"
  composer --working-dir="$EXT_DIR/../de.systopia.remotetools" update --no-dev --no-progress --prefer-dist --optimize-autoloader

  cv ext:enable "$EXT_NAME"
fi

cd "$EXT_DIR"
composer update --no-progress --prefer-dist --optimize-autoloader
composer composer-phpunit -- update --no-progress --prefer-dist
