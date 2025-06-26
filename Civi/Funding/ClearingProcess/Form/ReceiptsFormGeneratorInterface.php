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

/**
 * @phpstan-type urlT string
 *
 * @phpstan-type clearingItemRecordT array{
 *   _id: int|null,
 *   _financePlanItemId: int,
 *   file?: urlT|null,
 *   receiptNumber?: string|null,
 *   receiptDate?: string|null,
 *   paymentDate?: string|null,
 *   recipient?: string|null,
 *   reason?: string|null,
 *   amount: float|int,
 *   amountAdmitted?: float|int|null,
 *   properties?: array<string, mixed>|null,
 * }
 *   Each record will be persisted as one ClearingCostItem or
 *   ClearingResourcesItem.
 *
 * @phpstan-type clearingItemT array{records: list<clearingItemRecordT>|array<string, clearingItemRecordT>}
 *   When using properties in 'records', the property names MUST NOT be changed
 *   later!
 *
 * @phpstan-type clearingItemsT array<int|string, clearingItemT>
 *   The property names MUST NOT be changed later!
 *
 * @phpstan-type receiptsFormDataT array{
 *   costItems?: clearingItemsT,
 *   resourcesItems?: clearingItemsT,
 * }
 *   When loading form data for the clearing form, it will be in the same shape
 *   as on submit.
 *
 * Implementations do not need to care about permissions. "readOnly" keyword
 * will be automatically set accordingly. The "_financePlanItemId" property must
 * have the "const" keyword. The property "amountAdmitted" must be defined,
 * but doesn't need to be required. Other optional properties doesn't need to be
 * defined.
 */
interface ReceiptsFormGeneratorInterface {

  /**
   * Returns a JSON Forms specification that has a JSON schema that validates
   * the data specified in receiptsFormDataT. For displaying the schema might
   * have additional properties, but only those documented in receiptsFormDataT
   * will be persisted. The UI schema might reference values from the
   * 'reportData' property, too.
   *
   * @throws \CRM_Core_Exception
   *
   * @see \Civi\Funding\ClearingProcess\Form\ReportFormFactoryInterface
   */
  public function generateReceiptsForm(ClearingProcessEntityBundle $clearingProcessBundle): JsonFormsFormInterface;

}
