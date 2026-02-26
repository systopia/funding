ALTER TABLE civicrm_funding_payout_process
  ADD `creation_date` timestamp NOT NULL AFTER `status`,
  ADD `modification_date` timestamp NOT NULL AFTER `creation_date`;
