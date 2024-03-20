ALTER TABLE civicrm_funding_application_snapshot
  ADD cost_items text NOT NULL AFTER request_data;

ALTER TABLE civicrm_funding_app_cost_item
  ADD data_pointer varchar(255) NOT NULL COMMENT 'JSON pointer to data in application data';

UPDATE civicrm_funding_app_cost_item SET identifier = REPLACE(identifier, '/', '.');

ALTER TABLE civicrm_funding_application_snapshot
  ADD resources_items text NOT NULL AFTER cost_items;

ALTER TABLE civicrm_funding_app_resources_item
  ADD data_pointer varchar(255) NOT NULL COMMENT 'JSON pointer to data in application data';

UPDATE civicrm_funding_app_resources_item SET identifier = REPLACE(identifier, '/', '.');

CREATE TABLE `civicrm_funding_application_civioffice_template` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique FundingApplicationCiviOfficeTemplate ID',
  `case_type_id` int unsigned NOT NULL COMMENT 'FK to FundingCaseType',
  `document_uri` varchar(255) NOT NULL COMMENT 'CiviOffice document URI',
  `label` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `UI_case_type_id_label`(case_type_id, label),
  CONSTRAINT FK_civicrm_funding_application_civioffice_template_case_type_id FOREIGN KEY (`case_type_id`) REFERENCES `civicrm_funding_case_type`(`id`) ON DELETE CASCADE
)
ENGINE=InnoDB;
