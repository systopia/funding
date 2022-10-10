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

DROP TABLE IF EXISTS `civicrm_funding_app_cost_item`;
DROP TABLE IF EXISTS `civicrm_funding_program_relationship`;
DROP TABLE IF EXISTS `civicrm_funding_program_contact_relation`;
DROP TABLE IF EXISTS `civicrm_funding_program`;
DROP TABLE IF EXISTS `civicrm_funding_case_type_program`;
DROP TABLE IF EXISTS `civicrm_funding_case_type`;
DROP TABLE IF EXISTS `civicrm_funding_case_contact_relation`;
DROP TABLE IF EXISTS `civicrm_funding_case`;
DROP TABLE IF EXISTS `civicrm_funding_app_resources_item`;
DROP TABLE IF EXISTS `civicrm_funding_application_process`;

SET FOREIGN_KEY_CHECKS=1;
-- /*******************************************************
-- *
-- * Create new tables
-- *
-- *******************************************************/

-- /*******************************************************
-- *
-- * civicrm_funding_program
-- *
-- *******************************************************/
CREATE TABLE `civicrm_funding_program` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique FundingProgram ID',
  `title` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `requests_start_date` date NOT NULL,
  `requests_end_date` date NOT NULL,
  `currency` varchar(10) NOT NULL,
  `budget` decimal(10,2) NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `index_title`(title)
)
ENGINE=InnoDB;

-- /*******************************************************
-- *
-- * civicrm_funding_case_type
-- *
-- *******************************************************/
CREATE TABLE `civicrm_funding_case_type` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique FundingCaseType ID',
  `title` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `properties` text NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `index_title`(title)
)
ENGINE=InnoDB;

-- /*******************************************************
-- *
-- * civicrm_funding_case
-- *
-- *******************************************************/
CREATE TABLE `civicrm_funding_case` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique FundingCase ID',
  `funding_program_id` int unsigned NOT NULL COMMENT 'FK to FundingProgram',
  `funding_case_type_id` int unsigned NOT NULL COMMENT 'FK to FundingCaseType',
  `status` varchar(64) NOT NULL,
  `creation_date` timestamp NOT NULL,
  `modification_date` timestamp NOT NULL,
  `recipient_contact_id` int unsigned NOT NULL COMMENT 'FK to Contact',
  PRIMARY KEY (`id`),
  CONSTRAINT FK_civicrm_funding_case_funding_program_id FOREIGN KEY (`funding_program_id`) REFERENCES `civicrm_funding_program`(`id`) ON DELETE RESTRICT,
  CONSTRAINT FK_civicrm_funding_case_funding_case_type_id FOREIGN KEY (`funding_case_type_id`) REFERENCES `civicrm_funding_case_type`(`id`) ON DELETE RESTRICT,
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
  `entity_table` varchar(64) NOT NULL COMMENT 'Table referenced by ID in `entity_id',
  `entity_id` int unsigned NOT NULL COMMENT 'ID of entity in `entity_table`',
  `parent_id` int unsigned COMMENT 'FK to FundingCaseContactRelation',
  `permissions` varchar(512) COMMENT 'Permissions as JSON array',
  PRIMARY KEY (`id`),
  CONSTRAINT FK_civicrm_funding_case_contact_relation_funding_case_id FOREIGN KEY (`funding_case_id`) REFERENCES `civicrm_funding_case`(`id`) ON DELETE CASCADE,
  CONSTRAINT FK_civicrm_funding_case_contact_relation_parent_id FOREIGN KEY (`parent_id`) REFERENCES `civicrm_funding_case_contact_relation`(`id`) ON DELETE RESTRICT
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
-- * civicrm_funding_program_contact_relation
-- *
-- * Defines who is allowed to access a funding program
-- *
-- *******************************************************/
CREATE TABLE `civicrm_funding_program_contact_relation` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique FundingProgramContactRelation ID',
  `funding_program_id` int unsigned NOT NULL COMMENT 'FK to FundingProgram',
  `entity_table` varchar(64) NOT NULL COMMENT 'Table referenced by ID in `entity_id',
  `entity_id` int unsigned NOT NULL COMMENT 'ID of entity in `entity_table`',
  `parent_id` int unsigned COMMENT 'FK to FundingProgramContactRelation',
  `permissions` varchar(512) COMMENT 'Permissions as JSON array',
  PRIMARY KEY (`id`),
  CONSTRAINT FK_civicrm_funding_program_contact_relation_funding_program_id FOREIGN KEY (`funding_program_id`) REFERENCES `civicrm_funding_program`(`id`) ON DELETE CASCADE,
  CONSTRAINT FK_civicrm_funding_program_contact_relation_parent_id FOREIGN KEY (`parent_id`) REFERENCES `civicrm_funding_program_contact_relation`(`id`) ON DELETE RESTRICT
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
-- * civicrm_funding_application_process
-- *
-- *******************************************************/
CREATE TABLE `civicrm_funding_application_process` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique FundingApplicationProcess ID',
  `funding_case_id` int unsigned COMMENT 'FK to FundingCase',
  `status` varchar(64) NOT NULL,
  `creation_date` timestamp NOT NULL,
  `modification_date` timestamp NOT NULL,
  `title` varchar(255) NOT NULL,
  `short_description` varchar(255) NOT NULL,
  `start_date` timestamp NULL,
  `end_date` timestamp NULL,
  `request_data` text NOT NULL,
  `amount_requested` decimal(10,2) NOT NULL,
  `amount_granted` decimal(10,2) NULL,
  `granted_budget` decimal(10,2) NULL,
  `is_review_content` tinyint NULL,
  `reviewer_cont_contact_id` int unsigned NULL COMMENT 'FK to Contact',
  `is_review_calculative` tinyint NULL,
  `reviewer_calc_contact_id` int unsigned NULL COMMENT 'FK to Contact',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `index_title`(title),
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
  PRIMARY KEY (`id`),
  UNIQUE INDEX `index_identifier_application_process_id`(identifier, application_process_id),
  CONSTRAINT FK_civicrm_funding_app_resources_item_application_process_id FOREIGN KEY (`application_process_id`) REFERENCES `civicrm_funding_application_process`(`id`) ON DELETE CASCADE
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
  PRIMARY KEY (`id`),
  UNIQUE INDEX `index_identifier_application_process_id`(identifier, application_process_id),
  CONSTRAINT FK_civicrm_funding_app_cost_item_application_process_id FOREIGN KEY (`application_process_id`) REFERENCES `civicrm_funding_application_process`(`id`) ON DELETE CASCADE
)
ENGINE=InnoDB;
