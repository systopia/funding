ALTER TABLE civicrm_funding_application_process
  ADD `amount_eligible` decimal(10,2) NOT NULL AFTER `amount_requested`;

UPDATE civicrm_funding_application_process SET amount_eligible = amount_requested WHERE is_eligible = 1;

ALTER TABLE civicrm_funding_application_snapshot
  ADD `amount_eligible` decimal(10,2) NOT NULL AFTER `amount_requested`;

UPDATE civicrm_funding_application_snapshot SET amount_eligible = amount_requested WHERE is_eligible = 1;
