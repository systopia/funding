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

namespace Civi\Funding\ApplicationProcess\Api4\ActionHandler;

use Civi\API\Exception\UnauthorizedException;
use Civi\Funding\ActivityTypeNames;
use Civi\Funding\Api4\Action\Remote\ApplicationProcess\AddApplicantCommentAction;
use Civi\Funding\ApplicationProcess\ActionsDeterminer\ApplicationProcessActionsDeterminerInterface;
use Civi\Funding\ApplicationProcess\ApplicationProcessActivityManager;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\Entity\ActivityEntity;
use Civi\Funding\Entity\FullApplicationProcessStatus;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\Api4\ActionHandler\AddApplicantCommentActionHandler
 */
final class AddApplicantCommentActionHandlerTest extends TestCase {

  private AddApplicantCommentActionHandler $actionHandler;

  private ApplicationProcessActionsDeterminerInterface&MockObject $actionsDeterminerMock;

  private MockObject&ApplicationProcessActivityManager $activityManagerMock;

  private ApplicationProcessManager&MockObject $applicationProcessManagerMock;

  protected function setUp(): void {
    parent::setUp();
    $this->actionsDeterminerMock = $this->createMock(ApplicationProcessActionsDeterminerInterface::class);
    $this->activityManagerMock = $this->createMock(ApplicationProcessActivityManager::class);
    $this->applicationProcessManagerMock = $this->createMock(ApplicationProcessManager::class);
    $this->actionHandler = new AddApplicantCommentActionHandler(
      $this->actionsDeterminerMock,
      $this->activityManagerMock,
      $this->applicationProcessManagerMock
    );
  }

  public function testAddApplicantComment(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::create();
    $applicationProcess = $applicationProcessBundle->getApplicationProcess();

    $action = (new AddApplicantCommentAction())
      ->setApplicationProcessId($applicationProcess->getId())
      ->setText("> test\nsecond line");

    $this->applicationProcessManagerMock->method('getBundle')
      ->with($applicationProcess->getId())
      ->willReturn($applicationProcessBundle);

    $statusList = [23 => new FullApplicationProcessStatus('status', NULL, NULL)];
    $this->applicationProcessManagerMock->method('getStatusList')
      ->with($applicationProcessBundle)
      ->willReturn($statusList);

    $this->actionsDeterminerMock->method('isActionAllowed')
      ->with('add-applicant-comment', $applicationProcessBundle, $statusList)
      ->willReturn(TRUE);

    $activity = ActivityEntity::fromArray([
      'activity_type_id:name' => ActivityTypeNames::FUNDING_APPLICATION_COMMENT_APPLICANT,
      'subject' => 'Funding Application Comment',
      'details' => '&gt; test<br>second line',
    ]);
    $this->activityManagerMock->expects(self::once())->method('addActivity')
      ->with($applicationProcess, $activity);

    static::assertSame($activity->toArray(), $this->actionHandler->addApplicantComment($action));
  }

  public function testNotAllowed(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::create();
    $applicationProcess = $applicationProcessBundle->getApplicationProcess();

    $action = (new AddApplicantCommentAction())
      ->setApplicationProcessId($applicationProcess->getId())
      ->setText('test');

    $this->applicationProcessManagerMock->method('getBundle')
      ->with($applicationProcess->getId())
      ->willReturn($applicationProcessBundle);

    $statusList = [23 => new FullApplicationProcessStatus('status', NULL, NULL)];
    $this->applicationProcessManagerMock->method('getStatusList')
      ->with($applicationProcessBundle)
      ->willReturn($statusList);

    $this->actionsDeterminerMock->method('isActionAllowed')
      ->with('add-applicant-comment', $applicationProcessBundle, $statusList)
      ->willReturn(FALSE);

    $this->activityManagerMock->expects(self::never())->method('addActivity');

    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage(
      "Permission to add an applicant comment to application process {$applicationProcess->getId()} is missing"
    );
    $this->actionHandler->addApplicantComment($action);
  }

}
