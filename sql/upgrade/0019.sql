CREATE TABLE `civicrm_funding_form_string_translation` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique FundingFormStringTranslation ID',
  `funding_program_id` int unsigned NOT NULL COMMENT 'FK to FundingProgram',
  `funding_case_type_id` int unsigned NOT NULL COMMENT 'FK to FundingCaseType',
  `msg_text` varchar(8000) COLLATE utf8mb4_bin NOT NULL COMMENT 'Original',
  `new_text` varchar(8000) COLLATE utf8mb4_bin NOT NULL COMMENT 'Translation',
  `modification_date` timestamp ON UPDATE CURRENT_TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT FK_civicrm_funding_form_string_translation_funding_program_id FOREIGN KEY (`funding_program_id`) REFERENCES `civicrm_funding_program`(`id`) ON DELETE CASCADE,
  CONSTRAINT FK_civicrm_funding_form_string_translation_funding_case_type_id FOREIGN KEY (`funding_case_type_id`) REFERENCES `civicrm_funding_case_type`(`id`) ON DELETE CASCADE)
  ENGINE=InnoDB;
