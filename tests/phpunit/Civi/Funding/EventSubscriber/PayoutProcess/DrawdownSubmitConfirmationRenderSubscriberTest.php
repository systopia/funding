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
use Civi\Funding\EntityFactory\AttachmentFactory;
use Civi\Funding\EntityFactory\DrawdownBundleFactory;
use Civi\Funding\Event\PayoutProcess\DrawdownCreatedEvent;
use Civi\Funding\FileTypeNames;
use Civi\Funding\FundingAttachmentManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\PayoutProcess\DrawdownSubmitConfirmationRenderSubscriber
 */
final class DrawdownSubmitConfirmationRenderSubscriberTest extends TestCase {

  private FundingAttachmentManagerInterface&MockObject $attachmentManagerMock;

  private MockObject&DocumentRendererInterface $documentRendererMock;

  private DrawdownSubmitConfirmationRenderSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->attachmentManagerMock = $this->createMock(FundingAttachmentManagerInterface::class);
    $this->documentRendererMock = $this->createMock(DocumentRendererInterface::class);
    $this->subscriber = new DrawdownSubmitConfirmationRenderSubscriber(
      $this->attachmentManagerMock,
      $this->documentRendererMock
    );
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      DrawdownCreatedEvent::class => 'onCreated',
    ];

    static::assertEquals($expectedSubscriptions, $this->subscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as $method) {
      static::assertTrue(method_exists($this->subscriber, $method));
    }
  }

  public function testOnCreated(): void {
    $drawdownBundle = DrawdownBundleFactory::create();
    $fundingCaseTypeId = $drawdownBundle->getFundingCaseType()->getId();
    $drawdownId = $drawdownBundle->getDrawdown()->getId();

    $templateAttachment = AttachmentFactory::create([
      'entity_table' => 'civicrm_funding_case_type',
      'entity_id' => $fundingCaseTypeId,
      'path' => '/path/to/template',
    ]);
    $this->attachmentManagerMock->expects(static::once())->method('getLastByFileType')
      ->with('civicrm_funding_case_type', $fundingCaseTypeId, FileTypeNames::DRAWDOWN_SUBMIT_CONFIRMATION_TEMPLATE)
      ->willReturn($templateAttachment);

    $this->documentRendererMock->expects(static::once())->method('render')
      ->with('/path/to/template', 'FundingDrawdown', $drawdownId, [
        'drawdown' => $drawdownBundle->getDrawdown(),
        'payoutProcess' => $drawdownBundle->getPayoutProcess(),
        'fundingCase' => $drawdownBundle->getFundingCase(),
        'fundingCaseType' => $drawdownBundle->getFundingCaseType(),
        'fundingProgram' => $drawdownBundle->getFundingProgram(),
      ])
      ->willReturn('/path/to/rendered-file.ext');

    $renderedFileAttachment = AttachmentFactory::create([
      'entity_table' => 'civicrm_funding_drawdown',
      'entity_id' => $drawdownId,
    ]);
    $this->attachmentManagerMock->expects(static::once())->method('attachFileUniqueByFileType')
      ->with(
        'civicrm_funding_drawdown',
        $drawdownBundle->getDrawdown()->getId(),
        FileTypeNames::DRAWDOWN_SUBMIT_CONFIRMATION,
        '/path/to/rendered-file.ext',
        $this->documentRendererMock->getMimeType(),
        ['name' => "drawdown-submit-confirmation.$drawdownId.ext"]
      )
      ->willReturn($renderedFileAttachment);

    $this->subscriber->onCreated(new DrawdownCreatedEvent($drawdownBundle));
  }

}
