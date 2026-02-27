ALTER TABLE civicrm_funding_application_process
  ADD `amount_eligible` decimal(10,2) NOT NULL AFTER `amount_requested`;
