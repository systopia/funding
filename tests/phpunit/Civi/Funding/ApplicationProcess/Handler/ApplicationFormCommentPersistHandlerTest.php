<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

namespace Civi\Funding\ApplicationProcess\Handler;

use Civi\Funding\ActivityTypeIds;
use Civi\Funding\ApplicationProcess\ApplicationProcessActivityManager;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormCommentPersistCommand;
use Civi\Funding\Entity\ActivityEntity;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\Funding\Mock\Form\ValidatedApplicationDataMock;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\Handler\ApplicationFormCommentPersistHandler
 */
final class ApplicationFormCommentPersistHandlerTest extends TestCase {

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationProcessActivityManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $activityManagerMock;

  private ApplicationFormCommentPersistHandler $handler;

  protected function setUp(): void {
    parent::setUp();
    $this->activityManagerMock = $this->createMock(ApplicationProcessActivityManager::class);
    $this->handler = new ApplicationFormCommentPersistHandler($this->activityManagerMock);
  }

  public function testHandleInternal(): void {
    $command = new ApplicationFormCommentPersistCommand(
      1,
      ApplicationProcessFactory::createApplicationProcess(),
      FundingCaseFactory::createFundingCase(),
      FundingCaseTypeFactory::createFundingCaseType(),
      FundingProgramFactory::createFundingProgram(),
      new ValidatedApplicationDataMock([], ['comment' => ['text' => "Test >\ncomment", 'type' => 'internal']]),
    );

    $this->activityManagerMock->expects(static::once())->method('addActivity')
      ->with(
        $command->getContactId(),
        $command->getApplicationProcess(),
        static::callback(function (ActivityEntity $activity) {
          static::assertSame(ActivityTypeIds::FUNDING_APPLICATION_COMMENT_INTERNAL, $activity->getActivityTypeId());
          static::assertSame('Test &gt;<br>comment', $activity->getDetails());
          static::assertSame('Funding Application Comment', $activity->getSubject());
          static::assertSame(
            ValidatedApplicationDataMock::ACTION,
            $activity->get('funding_application_comment.action')
          );

          return TRUE;
        })
      );

    $this->handler->handle($command);
  }

  public function testHandleExternal(): void {
    $command = new ApplicationFormCommentPersistCommand(
      1,
      ApplicationProcessFactory::createApplicationProcess(),
      FundingCaseFactory::createFundingCase(),
      FundingCaseTypeFactory::createFundingCaseType(),
      FundingProgramFactory::createFundingProgram(),
      new ValidatedApplicationDataMock([], ['comment' => ['text' => "Test >\ncomment", 'type' => 'external']]),
    );

    $this->activityManagerMock->expects(static::once())->method('addActivity')
      ->with(
        $command->getContactId(),
        $command->getApplicationProcess(),
        static::callback(function (ActivityEntity $activity) {
          static::assertSame(ActivityTypeIds::FUNDING_APPLICATION_COMMENT_EXTERNAL, $activity->getActivityTypeId());
          static::assertSame('Test &gt;<br>comment', $activity->getDetails());
          static::assertSame('Funding Application Comment', $activity->getSubject());
          static::assertSame(
            ValidatedApplicationDataMock::ACTION,
            $activity->get('funding_application_comment.action')
          );

          return TRUE;
        })
      );

    $this->handler->handle($command);
  }

}
