ALTER TABLE civicrm_funding_clearing_process
  MODIFY `creation_date` timestamp NULL COMMENT 'Start of the clearing. (Not date of entity creation.)',
  MODIFY `modification_date` timestamp NULL;
