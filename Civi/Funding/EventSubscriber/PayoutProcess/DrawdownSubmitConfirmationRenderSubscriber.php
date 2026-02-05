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

namespace Civi\Funding\EventSubscriber\PayoutProcess;

use Civi\Funding\DocumentRender\DocumentRendererInterface;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Event\PayoutProcess\DrawdownCreatedEvent;
use Civi\Funding\FileTypeNames;
use Civi\Funding\FundingAttachmentManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DrawdownSubmitConfirmationRenderSubscriber implements EventSubscriberInterface {

  private FundingAttachmentManagerInterface $attachmentManager;

  private DocumentRendererInterface $documentRenderer;

  public function __construct(
    FundingAttachmentManagerInterface $attachmentManager,
    DocumentRendererInterface $documentRenderer
  ) {
    $this->attachmentManager = $attachmentManager;
    $this->documentRenderer = $documentRenderer;
  }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents() {
    return [DrawdownCreatedEvent::class => 'onCreated'];
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onCreated(DrawdownCreatedEvent $event): void {
    $templateFile = $this->getTemplateFile($event->getFundingCaseType());
    if (NULL === $templateFile) {
      return;
    }

    $drawdown = $event->getDrawdown();
    $filename = $this->documentRenderer->render(
      $templateFile,
      'FundingDrawdown',
      $event->getDrawdown()->getId(),
      [
        'drawdown' => $drawdown,
        'payoutProcess' => $event->getPayoutProcess(),
        'fundingCase' => $event->getFundingCase(),
        'fundingCaseType' => $event->getFundingCaseType(),
        'fundingProgram' => $event->getFundingProgram(),
      ],
    );

    $this->attachmentManager->attachFileUniqueByFileType(
      'civicrm_funding_drawdown',
      $drawdown->getId(),
      FileTypeNames::DRAWDOWN_SUBMIT_CONFIRMATION,
      $filename,
      $this->documentRenderer->getMimeType(),
      [
        'name' => sprintf(
          'drawdown-submit-confirmation.%d.%s',
          $drawdown->getId(),
          pathinfo($filename, PATHINFO_EXTENSION)
        ),
      ],
    );
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function getTemplateFile(FundingCaseTypeEntity $fundingCaseType): ?string {
    $attachment = $this->attachmentManager->getLastByFileType(
      'civicrm_funding_case_type',
      $fundingCaseType->getId(),
      FileTypeNames::DRAWDOWN_SUBMIT_CONFIRMATION_TEMPLATE,
    );

    return $attachment?->getPath();
  }

}
