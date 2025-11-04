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

namespace Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Clearing;

use Assert\Assertion;
use Civi\Funding\ApplicationProcess\ApplicationCostItemManager;
use Civi\Funding\ClearingProcess\Form\ReceiptsFormGeneratorInterface;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\Form\JsonFormsForm;
use Civi\Funding\Form\JsonFormsFormInterface;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Clearing\JsonSchema\HiHReceiptsJsonSchema;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Clearing\UiSchema\HiHReceiptsUiSchema;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Traits\HiHSupportedFundingCaseTypesTrait;
use Civi\Funding\PayoutProcess\PayoutProcessManager;

final class HiHClearingReceiptsFormGenerator implements ReceiptsFormGeneratorInterface {

  use HiHSupportedFundingCaseTypesTrait;

  private ApplicationCostItemManager $applicationCostItemManager;

  private PayoutProcessManager $payoutProcessManager;

  public function __construct(
    ApplicationCostItemManager $applicationCostItemManager,
    PayoutProcessManager $payoutProcessManager
  ) {
    $this->applicationCostItemManager = $applicationCostItemManager;
    $this->payoutProcessManager = $payoutProcessManager;
  }

  /**
   * @inheritDoc
   */
  public function generateReceiptsForm(ClearingProcessEntityBundle $clearingProcessBundle): JsonFormsFormInterface {
    $applicationCostItems = $this->applicationCostItemManager->getByApplicationProcessId(
      $clearingProcessBundle->getApplicationProcess()->getId()
    );

    $applicationCostItemsByType = [];
    foreach ($applicationCostItems as $applicationCostItem) {
      [$type] = explode('.', $applicationCostItem->getType());
      $applicationCostItemsByType[$type][$applicationCostItem->getIdentifier()] = $applicationCostItem;
    }

    $payoutProcess = $this->payoutProcessManager->getLastByFundingCaseId(
      $clearingProcessBundle->getFundingCase()->getId()
    );
    Assertion::notNull($payoutProcess);

    return new JsonFormsForm(
      new HiHReceiptsJsonSchema(
        $applicationCostItemsByType['bewilligt']['personalkostenBewilligt'],
        $applicationCostItemsByType['bewilligt']['honorareBewilligt'],
        $applicationCostItemsByType['bewilligt']['sachkostenBewilligt'],
        $clearingProcessBundle,
      ),
      new HiHReceiptsUiSchema(
        $applicationCostItemsByType,
        $clearingProcessBundle
      ),
    );
  }

}
