ALTER TABLE civicrm_funding_application_process
  ADD `is_in_work` tinyint NOT NULL COMMENT 'Is the application in work by the applicant?' AFTER `is_eligible`,
  ADD `is_rejected` tinyint NOT NULL AFTER `is_in_work`,
  ADD `is_withdrawn` tinyint NOT NULL AFTER `is_rejected`,
  ADD INDEX `index_is_eligible`(is_eligible),
  ADD INDEX `index_is_in_work`(is_in_work),
  ADD INDEX `index_is_rejected`(is_rejected),
  ADD INDEX `index_is_withdrawn`(is_withdrawn);

ALTER TABLE civicrm_funding_application_snapshot
  ADD `is_in_work` tinyint NOT NULL AFTER `is_eligible`,
  ADD `is_rejected` tinyint NOT NULL AFTER `is_in_work`,
  ADD `is_withdrawn` tinyint NOT NULL AFTER `is_rejected`;
