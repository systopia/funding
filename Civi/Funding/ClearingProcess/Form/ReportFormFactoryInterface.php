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
use Civi\Funding\Form\JsonFormsFormInterface;

interface ReportFormFactoryInterface {

  public const SERVICE_TAG = 'funding.clearing.report_form_factory';

  /**
   * Creates a JSON Forms specification for the property 'reportData' of the
   * clearing form. The JSON schema should only contain that property. (Other
   * ones are not persisted.)
   *
   * Complete validation should not be done if the property '_action' contains
   * 'save', so it's possible to persist a state that's not yet ready for
   * review. This might be achieved using if-then-else or implications in JSON
   * schema. See
   * https://json-schema.org/understanding-json-schema/reference/conditionals#ifthenelse
   * Please note that conditionals are not used for form rendering. So every
   * property must also be specified outside a conditional.
   *
   * Additionally, it's possible to provide an implementation of
   * ClearingFormValidatorInterface.
   *
   * @see \Civi\Funding\ClearingProcess\Form\Validation\ClearingFormValidatorInterface
   */
  public function createReportForm(ClearingProcessEntityBundle $clearingProcessBundle): JsonFormsFormInterface;

}
