-- Required as long as MariaDB <= 10.9 is supported!
-- https://mariadb.com/kb/en/server-system-variables/#explicit_defaults_for_timestamp
SET SESSION explicit_defaults_for_timestamp=ON;

ALTER TABLE civicrm_funding_payout_process
  ADD `creation_date` timestamp NOT NULL AFTER `status`,
  ADD `modification_date` timestamp NOT NULL AFTER `creation_date`;

UPDATE civicrm_funding_payout_process SET
  creation_date=(SELECT creation_date FROM civicrm_funding_case WHERE id = funding_case_id),
  modification_date=(SELECT modification_date FROM civicrm_funding_case WHERE id = funding_case_id);
