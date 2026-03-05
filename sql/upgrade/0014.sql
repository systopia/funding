ALTER TABLE civicrm_funding_clearing_process
  ADD start_date timestamp NULL COMMENT 'Actual start of the activity' AFTER modification_date,
  ADD end_date timestamp NULL COMMENT 'Actual end of the activity' AFTER start_date;
