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

use Civi\Core\CiviEventDispatcherInterface;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;
use Civi\Funding\FundingProgram\FundingProgramManager;
use Civi\Funding\Notification\Event\PreSendNotificationEvent;
use Webmozart\Assert\Assert;

final class NotificationSender {

  private CiviEventDispatcherInterface $eventDispatcher;

  private FundingCaseTypeManager $fundingCaseTypeManager;

  private FundingProgramManager $fundingProgramManager;

  private NotificationSendTemplateParamsFactory $sendTemplateParamsFactory;

  private NotificationWorkflowDeterminer $workflowDeterminer;

  public function __construct(
    CiviEventDispatcherInterface $eventDispatcher,
    FundingCaseTypeManager $fundingCaseTypeManager,
    FundingProgramManager $fundingProgramManager,
    NotificationSendTemplateParamsFactory $sendTemplateParamsFactory,
    NotificationWorkflowDeterminer $workflowDeterminer
  ) {
    $this->eventDispatcher = $eventDispatcher;
    $this->fundingCaseTypeManager = $fundingCaseTypeManager;
    $this->fundingProgramManager = $fundingProgramManager;
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
    $fundingProgram = $this->fundingProgramManager->get($fundingCase->getFundingProgramId());
    Assert::notNull($fundingProgram);

    $tokenContext['fundingCase'] = $fundingCase;
    $tokenContext['fundingCaseType'] = $fundingCaseType;
    $tokenContext['fundingProgram'] = $fundingProgram;

    $event = new PreSendNotificationEvent(
      $fundingCase->getNotificationContactIds(),
      $tokenContext,
      $this->workflowDeterminer->getWorkflowName($workflowNamePostfix, $fundingCaseType),
      $workflowNamePostfix
    );
    $this->eventDispatcher->dispatch(PreSendNotificationEvent::class, $event);

    if (NULL === $event->getWorkflowName()) {
      return;
    }

    [$fromName, $fromEmail] = \CRM_Core_BAO_Domain::getNameAndEmail();

    foreach ($event->getNotificationContactIds() as $notificationContactId) {
      $sendTemplateParams = $this->sendTemplateParamsFactory->createSendTemplateParams(
        $notificationContactId,
        $event->getWorkflowName(),
        $fromName,
        $fromEmail,
        $tokenContext
      );

      if (NULL !== $sendTemplateParams) {
        \CRM_Core_Transaction::addCallback(
          \CRM_Core_Transaction::PHASE_POST_COMMIT,
          fn () => \CRM_Core_BAO_MessageTemplate::sendTemplate($sendTemplateParams)
        );
      }
    }
  }

}
