<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify it under
 *  the terms of the GNU Affero General Public License as published by the Free
 *  Software Foundation, either version 3 of the License, or (at your option) any
 *  later version.
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
 * @phpstan-type clearingItemRecordT array{
 *   _id: int|null,
 *   file?: string|null,
 *   receiptNumber?: ?string,
 *   receiptDate?: ?string,
 *   paymentDate?: ?string,
 *   recipient?: ?string,
 *   reason?: ?string,
 *   amount: float|int,
 *   amountAdmitted: float|int|null,
 *   properties?: array<string, mixed>|null,
 * }
 *
 * @phpstan-type clearingFormDataT array{
 *   _action: string,
 *   costItems?: array<int, array{records: list<clearingItemRecordT>}>,
 *   costItemsAmountAdmitted?: float,
 *   costItemsAmountRecorded?: float,
 *   resourcesItems?: array<int, array{records: list<clearingItemRecordT>}>,
 *   resourcesItemsAdmountAdmitted?: float,
 *   resourcesItemsAmountRecorded?: float,
 *   reportData?: array<string, mixed>,
 *   comment?: array{text: string, type: 'internal'|'external'},
 * }
 *
 * This class generates a JSON Forms specification that has a JSON schema that
 * validates the data specified in clearingFormDataT. (For displaying purposes
 * costItems and resourcesItems have additional properties.)
 */
interface ClearingFormGeneratorInterface {

  /**
   * @throws \CRM_Core_Exception
   */
  public function generateForm(ClearingProcessEntityBundle $clearingProcessBundle): JsonFormsFormInterface;

}
