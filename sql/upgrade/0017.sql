-- Required as long as MariaDB <= 10.9 is supported!
-- https://mariadb.com/kb/en/server-system-variables/#explicit_defaults_for_timestamp
SET SESSION explicit_defaults_for_timestamp=ON;

ALTER TABLE civicrm_funding_payout_process
  ADD `creation_date` timestamp NOT NULL AFTER `status`,
  ADD `modification_date` timestamp NOT NULL AFTER `creation_date`;
