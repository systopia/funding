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

CREATE TABLE `civicrm_funding_clearing_process` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique FundingClearingProcess ID',
  `application_process_id` int unsigned NOT NULL COMMENT 'FK to FundingApplicationProcess',
  `status` varchar(64) NOT NULL,
  `creation_date` timestamp NOT NULL,
  `modification_date` timestamp NOT NULL,
  `report_data` text NOT NULL,
  `is_review_content` tinyint NULL,
  `reviewer_cont_contact_id` int unsigned NULL COMMENT 'FK to Contact',
  `is_review_calculative` tinyint NULL,
  `reviewer_calc_contact_id` int unsigned NULL COMMENT 'FK to Contact',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `UI_application_process_id`(application_process_id),
  CONSTRAINT FK_civicrm_funding_clearing_process_application_process_id FOREIGN KEY (`application_process_id`) REFERENCES `civicrm_funding_application_process`(`id`) ON DELETE CASCADE,
  CONSTRAINT FK_civicrm_funding_clearing_process_reviewer_cont_contact_id FOREIGN KEY (`reviewer_cont_contact_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE RESTRICT,
  CONSTRAINT FK_civicrm_funding_clearing_process_reviewer_calc_contact_id FOREIGN KEY (`reviewer_calc_contact_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE RESTRICT
)
ENGINE=InnoDB;

CREATE TABLE `civicrm_funding_clearing_resources_item` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique FundingClearingResourcesItem ID',
  `clearing_process_id` int unsigned NOT NULL COMMENT 'FK to FundingClearingProcess',
  `app_resources_item_id` int unsigned NOT NULL COMMENT 'FK to FundingApplicationResourcesItem',
  `status` varchar(64) NOT NULL,
  `file_id` int unsigned NULL COMMENT 'FK to File',
  `amount` decimal(10,2) NOT NULL,
  `amount_admitted` decimal(10,2),
  `description` Text NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT FK_civicrm_funding_clearing_resources_item_clearing_process_id FOREIGN KEY (`clearing_process_id`) REFERENCES `civicrm_funding_clearing_process`(`id`) ON DELETE RESTRICT,
  CONSTRAINT FK_civicrm_funding_clearing_resources_item_app_resources_item_id FOREIGN KEY (`app_resources_item_id`) REFERENCES `civicrm_funding_app_resources_item`(`id`) ON DELETE CASCADE,
  CONSTRAINT FK_civicrm_funding_clearing_resources_item_file_id FOREIGN KEY (`file_id`) REFERENCES `civicrm_file`(`id`) ON DELETE RESTRICT
)
ENGINE=InnoDB;

CREATE TABLE `civicrm_funding_clearing_cost_item` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique FundingClearingCostItem ID',
  `clearing_process_id` int unsigned NOT NULL COMMENT 'FK to FundingClearingProcess',
  `application_cost_item_id` int unsigned NOT NULL COMMENT 'FK to FundingApplicationResourcesItem',
  `status` varchar(64) NOT NULL,
  `file_id` int unsigned NULL COMMENT 'FK to File',
  `amount` decimal(10,2) NOT NULL,
  `amount_admitted` decimal(10,2),
  `description` Text NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT FK_civicrm_funding_clearing_cost_item_clearing_process_id FOREIGN KEY (`clearing_process_id`) REFERENCES `civicrm_funding_clearing_process`(`id`) ON DELETE RESTRICT,
  CONSTRAINT FK_civicrm_funding_clearing_cost_item_application_cost_item_id FOREIGN KEY (`application_cost_item_id`) REFERENCES `civicrm_funding_app_cost_item`(`id`) ON DELETE CASCADE,
  CONSTRAINT FK_civicrm_funding_clearing_cost_item_file_id FOREIGN KEY (`file_id`) REFERENCES `civicrm_file`(`id`) ON DELETE RESTRICT
)
ENGINE=InnoDB;
