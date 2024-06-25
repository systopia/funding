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

use Civi\Funding\Form\JsonFormsFormInterface;
use Civi\RemoteTools\JsonForms\JsonFormsElement;

interface ReportFormInterface extends JsonFormsFormInterface {

  /**
   * @return \Civi\RemoteTools\JsonForms\JsonFormsElement|null
   *   JSON Forms Element that is appended to the receipts form, or null.
   */
  public function getReceiptsAppendUiSchema(): ?JsonFormsElement;

  /**
   * @return \Civi\RemoteTools\JsonForms\JsonFormsElement|null
   *   JSON Forms Element that is prepended to the receipts form, or null.
   */
  public function getReceiptsPrependUiSchema(): ?JsonFormsElement;

  /**
   * The UI schema returned by getUiSchema() is displayed before the receipts
   * category. This one is displayed after the receipts category.
   */
  public function getPostReceiptsUiSchema(): ?JsonFormsElement;

}
