-- Required as long as MariaDB <= 10.9 is supported!
-- https://mariadb.com/kb/en/server-system-variables/#explicit_defaults_for_timestamp
SET SESSION explicit_defaults_for_timestamp=ON;

ALTER TABLE civicrm_funding_clearing_process
  ADD start_date timestamp NULL COMMENT 'Actual start of the activity' AFTER modification_date,
  ADD end_date timestamp NULL COMMENT 'Actual end of the activity' AFTER start_date;
