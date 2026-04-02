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

namespace Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Clearing;

use Civi\Core\Format;
use Civi\Funding\ApplicationProcess\ApplicationCostItemManager;
use Civi\Funding\ClearingProcess\Form\ReceiptsFormGeneratorInterface;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\Form\JsonFormsForm;
use Civi\Funding\Form\JsonFormsFormInterface;
use Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Clearing\JsonSchema\PersonalkostenReceiptsJsonSchema;
use Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Clearing\UiSchema\PersonalkostenReceiptsUiSchema;
use Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Traits\PersonalkostenSupportedFundingCaseTypesTrait;

final class PersonalkostenClearingReceiptsFormGenerator implements ReceiptsFormGeneratorInterface {

  use PersonalkostenSupportedFundingCaseTypesTrait;

  private ApplicationCostItemManager $applicationCostItemManager;

  private Format $format;

  public function __construct(ApplicationCostItemManager $applicationCostItemManager, Format $format) {
    $this->applicationCostItemManager = $applicationCostItemManager;
    $this->format = $format;
  }

  /**
   * @inheritDoc
   */
  public function generateReceiptsForm(ClearingProcessEntityBundle $clearingProcessBundle): JsonFormsFormInterface {
    $applicationCostItems = $this->applicationCostItemManager->getByApplicationProcessId(
      $clearingProcessBundle->getApplicationProcess()->getId()
    );

    $applicationCostItemsByIdentifier = [];
    foreach ($applicationCostItems as $applicationCostItem) {
      $applicationCostItemsByIdentifier[$applicationCostItem->getIdentifier()] = $applicationCostItem;
    }

    return new JsonFormsForm(
      new PersonalkostenReceiptsJsonSchema(
        $applicationCostItemsByIdentifier['personalkosten'],
        $applicationCostItemsByIdentifier['sachkostenpauschale'],
        $clearingProcessBundle,
      ),
      new PersonalkostenReceiptsUiSchema(
        $clearingProcessBundle,
        $this->format->money(
          $applicationCostItemsByIdentifier['personalkosten']->getAmount(),
          $clearingProcessBundle->getFundingProgram()->getCurrency()
        ),
      )
    );
  }

}
