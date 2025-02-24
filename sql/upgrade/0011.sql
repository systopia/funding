-- Required as long as MariaDB <= 10.9 is supported!
-- https://mariadb.com/kb/en/server-system-variables/#explicit_defaults_for_timestamp
SET SESSION explicit_defaults_for_timestamp=ON;

ALTER TABLE civicrm_funding_clearing_process
  MODIFY `creation_date` timestamp NULL COMMENT 'Start of the clearing. (Not date of entity creation.)',
  MODIFY `modification_date` timestamp NULL;
