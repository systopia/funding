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
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\Mock\Form\ValidatedApplicationDataMock;
use Civi\Funding\Mock\RequestContext\TestRequestContext;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\Handler\ApplicationFormCommentPersistHandler
 */
final class ApplicationFormCommentPersistHandlerTest extends TestCase {

  private ApplicationProcessActivityManager&MockObject $activityManagerMock;

  private ApplicationFormCommentPersistHandler $handler;

  protected function setUp(): void {
    parent::setUp();
    $this->activityManagerMock = $this->createMock(ApplicationProcessActivityManager::class);
    $this->handler = new ApplicationFormCommentPersistHandler(
      $this->activityManagerMock,
      TestRequestContext::newInternal(123)
    );
  }

  public function testHandleInternal(): void {
    $command = new ApplicationFormCommentPersistCommand(
      ApplicationProcessBundleFactory::createApplicationProcessBundle(),
      new ValidatedApplicationDataMock(
        [],
        [
          'comment' => [
            'text' => "Test >\ncomment",
            'type' => 'internal',
          ],
        ]
      ),
    );

    $this->activityManagerMock->expects(static::once())->method('addActivity')
      ->with(
        123,
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
      ApplicationProcessBundleFactory::createApplicationProcessBundle(),
      new ValidatedApplicationDataMock(
        [],
        [
          'comment' => [
            'text' => "Test >\ncomment",
            'type' => 'external',
          ],
        ]
      ),
    );

    $this->activityManagerMock->expects(static::once())->method('addActivity')
      ->with(
        123,
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
