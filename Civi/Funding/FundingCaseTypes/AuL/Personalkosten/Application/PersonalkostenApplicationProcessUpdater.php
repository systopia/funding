<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
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

declare(strict_types = 1);

namespace Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Application;

use Civi\Funding\ApplicationProcess\ApplicationCostItemManager;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormSubmitCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormSubmitHandlerInterface;
use Civi\Funding\ClearingProcess\ClearingCostItemManager;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;

final class PersonalkostenApplicationProcessUpdater {

  private ApplicationCostItemManager $applicationCostItemManager;

  private ApplicationProcessManager $applicationProcessManager;

  private ClearingCostItemManager $clearingCostItemManager;

  private ApplicationFormSubmitHandlerInterface $formSubmitHandler;

  public function __construct(
    ApplicationCostItemManager $applicationCostItemManager,
    ApplicationProcessManager $applicationProcessManager,
    ClearingCostItemManager $clearingCostItemManager,
    ApplicationFormSubmitHandlerInterface $formSubmitHandler
  ) {
    $this->applicationCostItemManager = $applicationCostItemManager;
    $this->applicationProcessManager = $applicationProcessManager;
    $this->clearingCostItemManager = $clearingCostItemManager;
    $this->formSubmitHandler = $formSubmitHandler;
  }

  public function updateApplicationProcess(
    ApplicationProcessEntityBundle $applicationProcessBundle,
    float $newFoerderquote,
    float $newSachkostenpauschale
  ): void {
    $applicationProcess = $applicationProcessBundle->getApplicationProcess();
    $this->formSubmitHandler->handle(new ApplicationFormSubmitCommand(
      $applicationProcessBundle, $this->applicationProcessManager->getStatusList($applicationProcessBundle), [
        '_action' => 'update',
        'sachkostenpauschale' => $newSachkostenpauschale,
        'foerderquote' => $newFoerderquote,
      ] + $applicationProcess->getRequestData(),
    ));

    $clearingCostItems = $this->clearingCostItemManager->getByApplicationProcessId($applicationProcess->getId());
    foreach ($clearingCostItems as $clearingCostItem) {
      $costItem = $this->applicationCostItemManager->get($clearingCostItem->getApplicationCostItemId());
      assert(NULL !== $costItem);
      if ($costItem->getIdentifier() === 'personalkosten') {
        if ($clearingCostItem->getStatus() === 'accepted') {
          $newPersonalkostenAmountAdmitted = round($newFoerderquote * $clearingCostItem->getAmount() / 100, 2);
          if ($clearingCostItem->getAmount() !== $newPersonalkostenAmountAdmitted) {
            $clearingCostItem->setAmountAdmitted($newPersonalkostenAmountAdmitted);
            $this->clearingCostItemManager->save($clearingCostItem);
          }
        }
      }
      elseif ($costItem->getIdentifier() === 'sachkostenpauschale') {
        if ($clearingCostItem->getAmount() !== $newSachkostenpauschale) {
          $clearingCostItem->setAmount($newSachkostenpauschale);
          if ($clearingCostItem->getStatus() === 'accepted') {
            $clearingCostItem->setAmountAdmitted($newSachkostenpauschale);
          }
          $this->clearingCostItemManager->save($clearingCostItem);
        }
      }
    }
  }

}
