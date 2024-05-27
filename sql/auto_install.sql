-- +--------------------------------------------------------------------+
-- | Copyright CiviCRM LLC. All rights reserved.                        |
-- |                                                                    |
-- | This work is published under the GNU AGPLv3 license with some      |
-- | permitted exceptions and without any warranty. For full license    |
-- | and copyright information, see https://civicrm.org/licensing       |
-- +--------------------------------------------------------------------+
--
-- Generated from schema.tpl
-- DO NOT EDIT.  Generated by CRM_Core_CodeGen
--
-- /*******************************************************
-- *
-- * Clean up the existing tables - this section generated from drop.tpl
-- *
-- *******************************************************/

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `civicrm_funding_clearing_cost_item`;
DROP TABLE IF EXISTS `civicrm_funding_app_cost_item`;
DROP TABLE IF EXISTS `civicrm_funding_drawdown`;
DROP TABLE IF EXISTS `civicrm_funding_clearing_resources_item`;
DROP TABLE IF EXISTS `civicrm_funding_clearing_process`;
DROP TABLE IF EXISTS `civicrm_funding_application_snapshot`;
DROP TABLE IF EXISTS `civicrm_funding_app_resources_item`;
DROP TABLE IF EXISTS `civicrm_funding_application_process`;
DROP TABLE IF EXISTS `civicrm_funding_payout_process`;
DROP TABLE IF EXISTS `civicrm_funding_new_case_permissions`;
DROP TABLE IF EXISTS `civicrm_funding_case_type_program`;
DROP TABLE IF EXISTS `civicrm_funding_case_permissions_cache`;
DROP TABLE IF EXISTS `civicrm_funding_case_contact_relation`;
DROP TABLE IF EXISTS `civicrm_funding_case`;
DROP TABLE IF EXISTS `civicrm_funding_application_civioffice_template`;
DROP TABLE IF EXISTS `civicrm_funding_recipient_contact_relation`;
DROP TABLE IF EXISTS `civicrm_funding_program_relationship`;
DROP TABLE IF EXISTS `civicrm_funding_program_contact_relation`;
DROP TABLE IF EXISTS `civicrm_funding_program`;
DROP TABLE IF EXISTS `civicrm_funding_case_type`;

SET FOREIGN_KEY_CHECKS=1;
-- /*******************************************************
-- *
-- * Create new tables
-- *
-- *******************************************************/

-- /*******************************************************
-- *
-- * civicrm_funding_case_type
-- *
-- *******************************************************/
CREATE TABLE `civicrm_funding_case_type` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique FundingCaseType ID',
  `title` varchar(255) NOT NULL,
  `abbreviation` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_combined_application` tinyint NOT NULL,
  `application_process_label` varchar(255) COMMENT 'Used for combined applications',
  `properties` text NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `index_title`(title),
  UNIQUE INDEX `index_abbreviation`(abbreviation)
)
ENGINE=InnoDB;

-- /*******************************************************
-- *
-- * civicrm_funding_program
-- *
-- *******************************************************/
CREATE TABLE `civicrm_funding_program` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique FundingProgram ID',
  `title` varchar(255) NOT NULL,
  `abbreviation` varchar(20) NOT NULL,
  `identifier_prefix` varchar(100) NOT NULL COMMENT 'The database ID of a funding case will be appended to this prefix and forms its identifier',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `requests_start_date` date NOT NULL,
  `requests_end_date` date NOT NULL,
  `currency` varchar(10) NOT NULL,
  `budget` decimal(10,2) NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `index_title`(title),
  UNIQUE INDEX `index_abbreviation`(abbreviation)
)
ENGINE=InnoDB;

-- /*******************************************************
-- *
-- * civicrm_funding_program_contact_relation
-- *
-- * Defines who is allowed to access a funding program
-- *
-- *******************************************************/
CREATE TABLE `civicrm_funding_program_contact_relation` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique FundingProgramContactRelation ID',
  `funding_program_id` int unsigned NOT NULL COMMENT 'FK to FundingProgram',
  `type` varchar(255) NOT NULL,
  `properties` text NOT NULL,
  `permissions` varchar(512) NOT NULL COMMENT 'Permissions as JSON array',
  PRIMARY KEY (`id`),
  CONSTRAINT FK_civicrm_funding_program_contact_relation_funding_program_id FOREIGN KEY (`funding_program_id`) REFERENCES `civicrm_funding_program`(`id`) ON DELETE CASCADE
)
ENGINE=InnoDB;

-- /*******************************************************
-- *
-- * civicrm_funding_program_relationship
-- *
-- * Stores relationships between FundingProgram entities
-- *
-- *******************************************************/
CREATE TABLE `civicrm_funding_program_relationship` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique FundingProgramRelationship ID',
  `id_a` int unsigned NOT NULL COMMENT 'FK to FundingProgram',
  `id_b` int unsigned NOT NULL COMMENT 'FK to FundingProgram',
  `type` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT FK_civicrm_funding_program_relationship_id_a FOREIGN KEY (`id_a`) REFERENCES `civicrm_funding_program`(`id`) ON DELETE CASCADE,
  CONSTRAINT FK_civicrm_funding_program_relationship_id_b FOREIGN KEY (`id_b`) REFERENCES `civicrm_funding_program`(`id`) ON DELETE CASCADE
)
ENGINE=InnoDB;

-- /*******************************************************
-- *
-- * civicrm_funding_recipient_contact_relation
-- *
-- * Defines the contacts from which the recipient of a funding can be chosen
-- *
-- *******************************************************/
CREATE TABLE `civicrm_funding_recipient_contact_relation` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique FundingRecipientContactRelation ID',
  `funding_program_id` int unsigned NOT NULL COMMENT 'FK to FundingProgram',
  `type` varchar(255) NOT NULL,
  `properties` text NOT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT FK_civicrm_funding_recipient_contact_relation_funding_program_id FOREIGN KEY (`funding_program_id`) REFERENCES `civicrm_funding_program`(`id`) ON DELETE CASCADE
)
ENGINE=InnoDB;

-- /*******************************************************
-- *
-- * civicrm_funding_application_civioffice_template
-- *
-- * Templates for use in application portal
-- *
-- *******************************************************/
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

-- /*******************************************************
-- *
-- * civicrm_funding_case
-- *
-- *******************************************************/
CREATE TABLE `civicrm_funding_case` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique FundingCase ID',
  `identifier` varchar(255) NOT NULL COMMENT 'Unique generated identifier',
  `funding_program_id` int unsigned NOT NULL COMMENT 'FK to FundingProgram',
  `funding_case_type_id` int unsigned NOT NULL COMMENT 'FK to FundingCaseType',
  `status` varchar(64) NOT NULL,
  `creation_date` timestamp NOT NULL,
  `modification_date` timestamp NOT NULL,
  `creation_contact_id` int unsigned NOT NULL COMMENT 'FK to Contact',
  `recipient_contact_id` int unsigned NOT NULL COMMENT 'FK to Contact',
  `amount_approved` decimal(10,2),
  PRIMARY KEY (`id`),
  UNIQUE INDEX `UI_identifier`(identifier),
  CONSTRAINT FK_civicrm_funding_case_funding_program_id FOREIGN KEY (`funding_program_id`) REFERENCES `civicrm_funding_program`(`id`) ON DELETE RESTRICT,
  CONSTRAINT FK_civicrm_funding_case_funding_case_type_id FOREIGN KEY (`funding_case_type_id`) REFERENCES `civicrm_funding_case_type`(`id`) ON DELETE RESTRICT,
  CONSTRAINT FK_civicrm_funding_case_creation_contact_id FOREIGN KEY (`creation_contact_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE RESTRICT,
  CONSTRAINT FK_civicrm_funding_case_recipient_contact_id FOREIGN KEY (`recipient_contact_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE RESTRICT
)
ENGINE=InnoDB;

-- /*******************************************************
-- *
-- * civicrm_funding_case_contact_relation
-- *
-- * Stores which permissions a contact or a related contact has on a funding case
-- *
-- *******************************************************/
CREATE TABLE `civicrm_funding_case_contact_relation` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique FundingCaseContactRelation ID',
  `funding_case_id` int unsigned NOT NULL COMMENT 'FK to FundingCase',
  `type` varchar(255) NOT NULL,
  `properties` text NOT NULL,
  `permissions` varchar(512) NOT NULL COMMENT 'Permissions as JSON array',
  PRIMARY KEY (`id`),
  CONSTRAINT FK_civicrm_funding_case_contact_relation_funding_case_id FOREIGN KEY (`funding_case_id`) REFERENCES `civicrm_funding_case`(`id`) ON DELETE CASCADE
)
ENGINE=InnoDB;

-- /*******************************************************
-- *
-- * civicrm_funding_case_permissions_cache
-- *
-- * Cache for FundingCase permissions
-- *
-- *******************************************************/
CREATE TABLE `civicrm_funding_case_permissions_cache` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique FundingCasePermissionsCache ID',
  `funding_case_id` int unsigned NOT NULL COMMENT 'FK to FundingCase',
  `contact_id` int unsigned NOT NULL COMMENT 'No FK to contact to work with 0 (contact ID on CLI), too',
  `is_remote` tinyint NOT NULL COMMENT 'Indicates whether the permissions are for internal or remote requests',
  `permissions` text COMMENT 'Permissions as JSON array. If NULL they have to be determined.',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `UI_funding_case_id_contact_id_is_remote`(funding_case_id, contact_id, is_remote),
  CONSTRAINT FK_civicrm_funding_case_permissions_cache_funding_case_id FOREIGN KEY (`funding_case_id`) REFERENCES `civicrm_funding_case`(`id`) ON DELETE CASCADE
)
ENGINE=InnoDB;

-- /*******************************************************
-- *
-- * civicrm_funding_case_type_program
-- *
-- * Stores which funding case types are available in a funding program
-- *
-- *******************************************************/
CREATE TABLE `civicrm_funding_case_type_program` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique FundingCaseTypeProgram ID',
  `funding_program_id` int unsigned NOT NULL COMMENT 'FK to FundingProgram',
  `funding_case_type_id` int unsigned NOT NULL COMMENT 'FK to FundingCaseType',
  PRIMARY KEY (`id`),
  CONSTRAINT FK_civicrm_funding_case_type_program_funding_program_id FOREIGN KEY (`funding_program_id`) REFERENCES `civicrm_funding_program`(`id`) ON DELETE CASCADE,
  CONSTRAINT FK_civicrm_funding_case_type_program_funding_case_type_id FOREIGN KEY (`funding_case_type_id`) REFERENCES `civicrm_funding_case_type`(`id`) ON DELETE RESTRICT
)
ENGINE=InnoDB;

-- /*******************************************************
-- *
-- * civicrm_funding_new_case_permissions
-- *
-- * Defines the initial permissions for new funding cases
-- *
-- *******************************************************/
CREATE TABLE `civicrm_funding_new_case_permissions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique FundingNewCasePermissions ID',
  `funding_program_id` int unsigned NOT NULL COMMENT 'FK to FundingProgram',
  `type` varchar(255) NOT NULL,
  `properties` text NOT NULL,
  `permissions` varchar(512) NOT NULL COMMENT 'Permissions as JSON array',
  PRIMARY KEY (`id`),
  CONSTRAINT FK_civicrm_funding_new_case_permissions_funding_program_id FOREIGN KEY (`funding_program_id`) REFERENCES `civicrm_funding_program`(`id`) ON DELETE CASCADE
)
ENGINE=InnoDB;

-- /*******************************************************
-- *
-- * civicrm_funding_payout_process
-- *
-- *******************************************************/
CREATE TABLE `civicrm_funding_payout_process` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique PayoutProcess ID',
  `funding_case_id` int unsigned NOT NULL COMMENT 'FK to FundingCase',
  `status` varchar(64) NOT NULL,
  `amount_total` decimal(10,2),
  PRIMARY KEY (`id`),
  CONSTRAINT FK_civicrm_funding_payout_process_funding_case_id FOREIGN KEY (`funding_case_id`) REFERENCES `civicrm_funding_case`(`id`) ON DELETE CASCADE
)
ENGINE=InnoDB;

-- /*******************************************************
-- *
-- * civicrm_funding_application_process
-- *
-- *******************************************************/
CREATE TABLE `civicrm_funding_application_process` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique FundingApplicationProcess ID',
  `identifier` varchar(255) NOT NULL COMMENT 'Unique generated identifier',
  `funding_case_id` int unsigned NOT NULL COMMENT 'FK to FundingCase',
  `status` varchar(64) NOT NULL,
  `creation_date` timestamp NOT NULL,
  `modification_date` timestamp NOT NULL,
  `title` varchar(255) NOT NULL,
  `short_description` varchar(500) NOT NULL,
  `start_date` timestamp NULL,
  `end_date` timestamp NULL,
  `request_data` text NOT NULL,
  `amount_requested` decimal(10,2) NOT NULL,
  `is_review_content` tinyint NULL,
  `reviewer_cont_contact_id` int unsigned NULL COMMENT 'FK to Contact',
  `is_review_calculative` tinyint NULL,
  `reviewer_calc_contact_id` int unsigned NULL COMMENT 'FK to Contact',
  `is_eligible` tinyint NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `index_identifier`(identifier),
  CONSTRAINT FK_civicrm_funding_application_process_funding_case_id FOREIGN KEY (`funding_case_id`) REFERENCES `civicrm_funding_case`(`id`) ON DELETE CASCADE,
  CONSTRAINT FK_civicrm_funding_application_process_reviewer_cont_contact_id FOREIGN KEY (`reviewer_cont_contact_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE RESTRICT,
  CONSTRAINT FK_civicrm_funding_application_process_reviewer_calc_contact_id FOREIGN KEY (`reviewer_calc_contact_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE RESTRICT
)
ENGINE=InnoDB;

-- /*******************************************************
-- *
-- * civicrm_funding_app_resources_item
-- *
-- *******************************************************/
CREATE TABLE `civicrm_funding_app_resources_item` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique FundingApplicationResourcesItem ID',
  `application_process_id` int unsigned NOT NULL COMMENT 'FK to FundingApplicationProcess',
  `identifier` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `properties` text NOT NULL,
  `data_pointer` varchar(255) NOT NULL COMMENT 'JSON pointer to data in application data',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `index_identifier_application_process_id`(identifier, application_process_id),
  CONSTRAINT FK_civicrm_funding_app_resources_item_application_process_id FOREIGN KEY (`application_process_id`) REFERENCES `civicrm_funding_application_process`(`id`) ON DELETE CASCADE
)
ENGINE=InnoDB;

-- /*******************************************************
-- *
-- * civicrm_funding_application_snapshot
-- *
-- * Snapshots of application versions that need to be preserved
-- *
-- *******************************************************/
CREATE TABLE `civicrm_funding_application_snapshot` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique FundingApplicationSnapshot ID',
  `application_process_id` int unsigned NOT NULL COMMENT 'FK to FundingApplicationProcess',
  `status` varchar(64) NOT NULL,
  `creation_date` timestamp NOT NULL,
  `title` varchar(255) NOT NULL,
  `short_description` varchar(255) NOT NULL,
  `start_date` timestamp NULL,
  `end_date` timestamp NULL,
  `request_data` text NOT NULL,
  `cost_items` text NOT NULL,
  `resources_items` text NOT NULL,
  `amount_requested` decimal(10,2) NOT NULL,
  `is_review_content` tinyint NULL,
  `is_review_calculative` tinyint NULL,
  `is_eligible` tinyint NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT FK_civicrm_funding_application_snapshot_application_process_id FOREIGN KEY (`application_process_id`) REFERENCES `civicrm_funding_application_process`(`id`) ON DELETE RESTRICT
)
ENGINE=InnoDB;

-- /*******************************************************
-- *
-- * civicrm_funding_clearing_process
-- *
-- *******************************************************/
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

-- /*******************************************************
-- *
-- * civicrm_funding_clearing_resources_item
-- *
-- * Clearing for an application resources item
-- *
-- *******************************************************/
CREATE TABLE `civicrm_funding_clearing_resources_item` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique FundingClearingResourcesItem ID',
  `clearing_process_id` int unsigned NOT NULL COMMENT 'FK to FundingClearingProcess',
  `app_resources_item_id` int unsigned NOT NULL COMMENT 'FK to FundingApplicationResourcesItem',
  `status` varchar(64) NOT NULL,
  `file_id` int unsigned NULL COMMENT 'FK to File',
  `receipt_number` varchar(255),
  `payment_date` date NOT NULL,
  `recipient` varchar(255) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `amount_admitted` decimal(10,2),
  PRIMARY KEY (`id`),
  CONSTRAINT FK_civicrm_funding_clearing_resources_item_clearing_process_id FOREIGN KEY (`clearing_process_id`) REFERENCES `civicrm_funding_clearing_process`(`id`) ON DELETE RESTRICT,
  CONSTRAINT FK_civicrm_funding_clearing_resources_item_app_resources_item_id FOREIGN KEY (`app_resources_item_id`) REFERENCES `civicrm_funding_app_resources_item`(`id`) ON DELETE CASCADE,
  CONSTRAINT FK_civicrm_funding_clearing_resources_item_file_id FOREIGN KEY (`file_id`) REFERENCES `civicrm_file`(`id`) ON DELETE RESTRICT
)
ENGINE=InnoDB;

-- /*******************************************************
-- *
-- * civicrm_funding_drawdown
-- *
-- * Drawdowns in a payout process
-- *
-- *******************************************************/
CREATE TABLE `civicrm_funding_drawdown` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique FundingDrawdown ID',
  `payout_process_id` int unsigned NOT NULL COMMENT 'FK to FundingPayoutProcess',
  `status` varchar(64) NOT NULL,
  `creation_date` timestamp NOT NULL,
  `amount` decimal(10,2),
  `acception_date` timestamp NULL,
  `requester_contact_id` int unsigned NOT NULL COMMENT 'FK to Contact',
  `reviewer_contact_id` int unsigned COMMENT 'FK to Contact',
  PRIMARY KEY (`id`),
  INDEX `index_status`(status),
  CONSTRAINT FK_civicrm_funding_drawdown_payout_process_id FOREIGN KEY (`payout_process_id`) REFERENCES `civicrm_funding_payout_process`(`id`) ON DELETE CASCADE,
  CONSTRAINT FK_civicrm_funding_drawdown_requester_contact_id FOREIGN KEY (`requester_contact_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE RESTRICT,
  CONSTRAINT FK_civicrm_funding_drawdown_reviewer_contact_id FOREIGN KEY (`reviewer_contact_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE RESTRICT
)
ENGINE=InnoDB;

-- /*******************************************************
-- *
-- * civicrm_funding_app_cost_item
-- *
-- *******************************************************/
CREATE TABLE `civicrm_funding_app_cost_item` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique FundingApplicationCostItem ID',
  `application_process_id` int unsigned NOT NULL COMMENT 'FK to FundingApplicationProcess',
  `identifier` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `properties` text NOT NULL,
  `data_pointer` varchar(255) NOT NULL COMMENT 'JSON pointer to data in application data',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `index_identifier_application_process_id`(identifier, application_process_id),
  CONSTRAINT FK_civicrm_funding_app_cost_item_application_process_id FOREIGN KEY (`application_process_id`) REFERENCES `civicrm_funding_application_process`(`id`) ON DELETE CASCADE
)
ENGINE=InnoDB;

-- /*******************************************************
-- *
-- * civicrm_funding_clearing_cost_item
-- *
-- * Clearing for an application cost item
-- *
-- *******************************************************/
CREATE TABLE `civicrm_funding_clearing_cost_item` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique FundingClearingCostItem ID',
  `clearing_process_id` int unsigned NOT NULL COMMENT 'FK to FundingClearingProcess',
  `application_cost_item_id` int unsigned NOT NULL COMMENT 'FK to FundingApplicationResourcesItem',
  `status` varchar(64) NOT NULL,
  `file_id` int unsigned NULL COMMENT 'FK to File',
  `receipt_number` varchar(255),
  `payment_date` date NOT NULL,
  `recipient` varchar(255) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `amount_admitted` decimal(10,2),
  PRIMARY KEY (`id`),
  CONSTRAINT FK_civicrm_funding_clearing_cost_item_clearing_process_id FOREIGN KEY (`clearing_process_id`) REFERENCES `civicrm_funding_clearing_process`(`id`) ON DELETE RESTRICT,
  CONSTRAINT FK_civicrm_funding_clearing_cost_item_application_cost_item_id FOREIGN KEY (`application_cost_item_id`) REFERENCES `civicrm_funding_app_cost_item`(`id`) ON DELETE CASCADE,
  CONSTRAINT FK_civicrm_funding_clearing_cost_item_file_id FOREIGN KEY (`file_id`) REFERENCES `civicrm_file`(`id`) ON DELETE RESTRICT
)
ENGINE=InnoDB;
