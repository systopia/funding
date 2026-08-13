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

namespace Civi\Funding\ApplicationProcess\BatchActionHandler;

use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseBundle;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\RemoteTools\RequestContext\RequestContextInterface;

final class MoveToNewFundingCaseHandler implements ApplicationProcessBatchActionHandlerInterface {

  public const ACTION = 'move-to-new-funding-case';

  public function __construct(
    private readonly ApplicationProcessManager $applicationProcessManager,
    private readonly FundingCaseManager $fundingCaseManager,
    private readonly RequestContextInterface $requestContext,
  ) {}

  public function handle(iterable $applicationProcessBundles): array {
    $result = [];
    $newFundingCases = [];
    foreach ($applicationProcessBundles as $applicationProcessBundle) {
      $applicationProcess = $applicationProcessBundle->getApplicationProcess();
      $fundingCase = $applicationProcessBundle->getFundingCase();
      $newFundingCase = $newFundingCases[$fundingCase->getId()] ??= $this->createFundingCase($applicationProcessBundle);
      $applicationProcess->setFundingCaseId($newFundingCase->getId());
      $applicationProcessBundle = new ApplicationProcessEntityBundle(
        $applicationProcess,
        $newFundingCase,
        $applicationProcessBundle->getFundingCaseType(),
        $applicationProcessBundle->getFundingProgram()
      );
      $this->applicationProcessManager->update($applicationProcessBundle);
      $result[$applicationProcess->getId()] = $applicationProcess->toArray();
    }

    return $result;
  }

  private function createFundingCase(FundingCaseBundle $applicationProcessBundle): FundingCaseEntity {
    $fundingCase = $applicationProcessBundle->getFundingCase();

    return $this->fundingCaseManager->create($this->requestContext->getContactId(), [
      'funding_case_type' => $applicationProcessBundle->getFundingCaseType(),
      'funding_program' => $applicationProcessBundle->getFundingProgram(),
      'recipient_contact_id' => $fundingCase->getRecipientContactId(),
      'notification_contact_ids' => $fundingCase->getNotificationContactIds(),
    ]);
  }

}
