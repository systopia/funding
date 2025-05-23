<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\Funding\FundingCaseTypes\BSH\HiHAktion\EventSubscriber;

use Civi\Api4\FundingApplicationCostItem;
use Civi\Api4\FundingPayoutProcess;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreUpdateEvent;
use Civi\Funding\Event\FundingCase\FundingCasePreUpdateEvent;
use Civi\Funding\Event\FundingCase\FundingCaseUpdatedEvent;
use Civi\Funding\FileTypeNames;
use Civi\Funding\FundingAttachmentManagerInterface;
use Civi\Funding\FundingCase\FundingCaseStatus;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\HiHConstants;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Query\CompositeCondition;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Approval is recalled.
 */
final class HiHRecallSubscriber implements EventSubscriberInterface {

  private Api4Interface $api4;

  private FundingAttachmentManagerInterface $attachmentManager;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    return [
      ApplicationProcessPreUpdateEvent::class => ['onApplicationProcessPreUpdate', -1000],
      FundingCasePreUpdateEvent::class => ['onPreUpdate', -1000],
      FundingCaseUpdatedEvent::class => ['onUpdated', -1000],
    ];
  }

  public function __construct(Api4Interface $api4, FundingAttachmentManagerInterface $attachmentManager) {
    $this->api4 = $api4;
    $this->attachmentManager = $attachmentManager;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onApplicationProcessPreUpdate(ApplicationProcessPreUpdateEvent $event): void {
    if ($event->getFundingCaseType()->getName() !== HiHConstants::FUNDING_CASE_TYPE_NAME) {
      return;
    }

    if (in_array($event->getPreviousApplicationProcess()->getStatus(), ['approved', 'approved_partial'], TRUE)
      && 'advisory' === $event->getApplicationProcess()->getStatus()
    ) {
      $requestData = $event->getApplicationProcess()->getRequestData();
      // @phpstan-ignore offsetAccess.nonOffsetAccessible
      unset($requestData['kosten']['personalkostenBewilligt']);
      // @phpstan-ignore offsetAccess.nonOffsetAccessible
      unset($requestData['kosten']['honorareBewilligt']);
      // @phpstan-ignore offsetAccess.nonOffsetAccessible
      unset($requestData['kosten']['sachkostenBewilligt']);
      // @phpstan-ignore offsetAccess.nonOffsetAccessible
      unset($requestData['kosten']['bewilligungskommentar']);
      $event->getApplicationProcess()->setRequestData($requestData);
      $event->getApplicationProcess()->setValues([
        'bsh_funding_application_extra.amount_approved_personalkosten' => NULL,
        'bsh_funding_application_extra.amount_approved_honorare' => NULL,
        'bsh_funding_application_extra.amount_approved_sachkosten' => NULL,
        'bsh_funding_application_extra.approval_comment' => NULL,
      ] + $event->getApplicationProcess()->toArray());

      $this->api4->deleteEntities(FundingApplicationCostItem::getEntityName(), CompositeCondition::new('AND',
        Comparison::new('application_process_id', '=', $event->getApplicationProcess()->getId()),
        Comparison::new('type', '=', 'bewilligt')
      ));
    }
  }

  public function onPreUpdate(FundingCasePreUpdateEvent $event): void {
    if ($event->getFundingCaseType()->getName() !== HiHConstants::FUNDING_CASE_TYPE_NAME) {
      return;
    }

    if ($event->getFundingCase()->getStatus() === FundingCaseStatus::OPEN
      && $event->getPreviousFundingCase()->getStatus() === FundingCaseStatus::ONGOING
    ) {
      $event->getFundingCase()->setAmountApproved(NULL);
    }
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onUpdated(FundingCaseUpdatedEvent $event): void {
    if ($event->getFundingCaseType()->getName() !== HiHConstants::FUNDING_CASE_TYPE_NAME) {
      return;
    }

    if ($event->getFundingCase()->getStatus() === FundingCaseStatus::OPEN
      && $event->getPreviousFundingCase()->getStatus() === FundingCaseStatus::ONGOING
    ) {
      $this->api4->deleteEntities(
        FundingPayoutProcess::getEntityName(),
        Comparison::new('funding_case_id', '=', $event->getFundingCase()->getId())
      );

      $contractAttachments = $this->attachmentManager->getByFileType(
        'civicrm_funding_case',
        $event->getFundingCase()->getId(),
        FileTypeNames::TRANSFER_CONTRACT
      );
      foreach ($contractAttachments as $attachment) {
        $this->attachmentManager->delete($attachment);
      }
    }
  }

}
