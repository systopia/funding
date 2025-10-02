/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

CREATE TABLE `civicrm_funding_form_string_translation` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique FundingFormStringTranslation ID',
  `funding_program_id` int unsigned NOT NULL COMMENT 'FK to FundingProgram',
  `funding_case_type_id` int unsigned NOT NULL COMMENT 'FK to FundingCaseType',
  `msg_text` varchar(8000) COLLATE utf8mb4_bin NOT NULL COMMENT 'Original',
  `new_text` varchar(8000) COLLATE utf8mb4_bin NOT NULL COMMENT 'Translation',
  `modification_date` timestamp ON UPDATE CURRENT_TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `UI_translation`(funding_program_id, funding_case_type_id, msg_text),
  CONSTRAINT FK_civicrm_funding_form_string_translation_funding_program_id FOREIGN KEY (`funding_program_id`) REFERENCES `civicrm_funding_program`(`id`) ON DELETE CASCADE,
  CONSTRAINT FK_civicrm_funding_form_string_translation_funding_case_type_id FOREIGN KEY (`funding_case_type_id`) REFERENCES `civicrm_funding_case_type`(`id`) ON DELETE CASCADE)
  ENGINE=InnoDB;
