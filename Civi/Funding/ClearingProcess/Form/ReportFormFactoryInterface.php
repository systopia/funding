<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation in version 3.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\ClearingProcess\Form;

use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;

interface ReportFormFactoryInterface {

  public const SERVICE_TAG = 'funding.clearing.report_form_factory';

  /**
   * Creates a JSON Forms specification for the property 'reportData' of the
   * clearing form. The JSON schema should only contain that property. (Other
   * ones are not persisted.)
   *
   * The UI schema will be converted to a Categorization like this (unless the
   * JSON schema is empty):
   * - If it is a Categorization, its elements will be used.
   * - If it is a Category it will be used.
   * - Otherwise a new Category will be created and used in a new Categorization.
   *   The Category's label is taken from the UI schema, if any, or a fallback
   *   is used.
   *
   * To the Categorization a Category is added to provide the receipts for the
   * clearable cost items and resources items. The returned object might provide
   * JSON Forms elements that are appended or prepended to the receipts form.
   *
   * Complete validation should not be done if the property '_action' contains
   * 'save', so it's possible to persist a state that's not yet ready for
   * review. This might be achieved using if-then-else in JSON schema. See
   * https://json-schema.org/understanding-json-schema/reference/conditionals#ifthenelse
   * Please note that conditionals are not used for form rendering. So every
   * property must also be specified outside a conditional. ('oneOf' and 'anyOf'
   * should be avoided because those keywords cannot be mapped to a specific
   * form field.)
   *
   * Additionally, it's possible to provide an implementation of
   * ClearingFormValidatorInterface.
   *
   * @see \Civi\Funding\ClearingProcess\Form\Validation\ClearingFormValidatorInterface
   */
  public function createReportForm(ClearingProcessEntityBundle $clearingProcessBundle): ReportFormInterface;

  /**
   * The returned form is used to extract translatable strings. If there are
   * strings with dynamic content, they can be replaced by a JsonSchema object
   * with the keywords "text" and "values", and optionally "locale".
   * The keyword "text" contains the string with placeholders, and "values" a
   * mapping of placeholder to replacement, e.g. (JSON encoded):
   * { "text": "Hello {name}", "values": {"name": "World"} }
   *
   * For the replacement PHP's \MessageFormatter is used. This allows locale
   * aware number and date formatting. If the property "locale" is not given,
   * CiviCRMs locale will be used.
   *
   * There might be strings that are only visible under specific conditions.
   * Those can be set as a list of strings at the keyword "$translatableTexts"
   * in the UI schema.
   *
   *  If no translation is required, an empty form can be returned.
   *
   * @see \Civi\Funding\Translation\JsonSchemaStringExtractor
   * @see \Civi\Funding\Translation\UiSchemaStringExtractor
   * For the places where strings are extracted.
   *
   * @see \MessageFormatter::formatMessage()
   */
  public function createReportFormForTranslation(
    FundingProgramEntity $fundingProgram,
    FundingCaseTypeEntity $fundingCaseType
  ): ReportFormInterface;

}
