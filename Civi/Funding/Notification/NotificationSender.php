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

namespace Civi\Funding\Notification;

use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;
use Webmozart\Assert\Assert;

final class NotificationSender {

  private FundingCaseTypeManager $fundingCaseTypeManager;

  private NotificationSendTemplateParamsFactory $sendTemplateParamsFactory;

  private NotificationWorkflowDeterminer $workflowDeterminer;

  public function __construct(
    FundingCaseTypeManager $fundingCaseTypeManager,
    NotificationSendTemplateParamsFactory $sendTemplateParamsFactory,
    NotificationWorkflowDeterminer $workflowDeterminer
  ) {
    $this->fundingCaseTypeManager = $fundingCaseTypeManager;
    $this->sendTemplateParamsFactory = $sendTemplateParamsFactory;
    $this->workflowDeterminer = $workflowDeterminer;
  }

  /**
   * @phpstan-param array<string, mixed> $tokenContext
   *
   * @throws \CRM_Core_Exception
   */
  public function sendNotification(
    string $workflowNamePostfix,
    FundingCaseEntity $fundingCase,
    array $tokenContext
  ): void {
    $fundingCaseType = $this->fundingCaseTypeManager->get($fundingCase->getFundingCaseTypeId());
    Assert::notNull($fundingCaseType);

    $workflowName = $this->workflowDeterminer->getWorkflowName($workflowNamePostfix, $fundingCaseType);
    if (NULL === $workflowName) {
      return;
    }

    $tokenContext['fundingCase'] = $fundingCase;
    $tokenContext['fundingCaseType'] = $fundingCaseType;

    [$fromName, $fromEmail] = \CRM_Core_BAO_Domain::getNameAndEmail();

    foreach ($fundingCase->getNotificationContactIds() as $notificationContactId) {
      $sendTemplateParams = $this->sendTemplateParamsFactory->createSendTemplateParams(
        $notificationContactId,
        $workflowName,
        $fromName,
        $fromEmail,
        $tokenContext
      );

      if (NULL !== $sendTemplateParams) {
        \CRM_Core_BAO_MessageTemplate::sendTemplate($sendTemplateParams);
      }
    }
  }

}
