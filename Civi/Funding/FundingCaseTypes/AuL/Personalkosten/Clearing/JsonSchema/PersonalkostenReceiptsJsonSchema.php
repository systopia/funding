<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
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

namespace Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Clearing\JsonSchema;

use Civi\Funding\ClearingProcess\ClearingProcessPermissions;
use Civi\Funding\Entity\ApplicationCostItemEntity;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;

final class PersonalkostenReceiptsJsonSchema extends JsonSchemaObject {

  public function __construct(
    ApplicationCostItemEntity $personalkostenBeantragt,
    ApplicationCostItemEntity $sachkostenpauschale,
    ClearingProcessEntityBundle $clearingProcessBundle,
  ) {
    /** @var float $foerderquote */
    $foerderquote = $clearingProcessBundle->getFundingProgram()->get('funding_program_extra.foerderquote');
    $properties = [
      'costItems' => new PersonalkostenClearingCostItemsJsonSchema(
        $personalkostenBeantragt,
        $sachkostenpauschale,
        $clearingProcessBundle->getFundingCase()->hasPermission(ClearingProcessPermissions::REVIEW_CALCULATIVE),
        $foerderquote,
      ),
    ];
    parent::__construct($properties, ['required' => array_keys($properties)]);
  }

}
