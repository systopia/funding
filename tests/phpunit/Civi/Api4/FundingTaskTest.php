<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\Api4;

use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingCaseBundleFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Fixtures\FundingTaskFixture;
use Civi\Funding\Util\RequestTestUtil;

/**
 * @covers \Civi\Api4\FundingTask
 * @covers \Civi\Funding\Api4\Action\FundingTask\GetAction
 *
 * @group headless
 */
final class FundingTaskTest extends AbstractFundingHeadlessTestCase {

  public function testGetWithoutPermissions(): void {
    $fundingCaseBundle = FundingCaseBundleFixture::create();
    $fundingCase = $fundingCaseBundle->getFundingCase();
    $task = FundingTaskFixture::addFixture(
      $fundingCase->getCreationContactId(),
      $fundingCase->getId(),
      $fundingCase->getIdentifier()
    );

    $contact = ContactFixture::addIndividual();
    RequestTestUtil::mockInternalRequest($contact['id']);

    static::assertCount(0, FundingTask::get(FALSE)->execute());

    FundingCaseContactRelationFixture::addContact($contact['id'], $fundingCase->getId(), ['test']);
    /** @var array<string, mixed> $fetchedTask */
    $fetchedTask = FundingTask::get(FALSE)->execute()->single();
    static::assertSame($task->getId(), $fetchedTask['id']);

    /** @var array<string, mixed> $fetchedTask */
    $fetchedTask = FundingTask::get(FALSE)->execute()->single();
    static::assertSame($task->getId(), $fetchedTask['id']);
  }

  public function testGetWithPermissions(): void {
    $fundingCaseBundle = FundingCaseBundleFixture::create();
    $fundingCase = $fundingCaseBundle->getFundingCase();
    $task = FundingTaskFixture::addFixture(
      $fundingCase->getCreationContactId(),
      $fundingCase->getId(),
      $fundingCase->getIdentifier(),
      ['required_permissions' => ['task']]
    );

    $contact = ContactFixture::addIndividual();
    RequestTestUtil::mockInternalRequest($contact['id']);

    static::assertCount(0, FundingTask::get(FALSE)->execute());

    FundingCaseContactRelationFixture::addContact($contact['id'], $fundingCase->getId(), ['test']);
    static::assertCount(0, FundingTask::get(FALSE)->execute());

    /** @var array<string, mixed> $fetchedTask */
    $fetchedTask = FundingTask::get(FALSE)->setIgnoreTaskPermissions(TRUE)->execute()->single();
    static::assertSame($task->getId(), $fetchedTask['id']);

    FundingCaseContactRelationFixture::addContact($contact['id'], $fundingCase->getId(), ['task']);
    /** @var array<string, mixed> $fetchedTask */
    $fetchedTask = FundingTask::get(FALSE)->execute()->single();
    static::assertSame($task->getId(), $fetchedTask['id']);

    /** @var array<string, mixed> $fetchedTask */
    $fetchedTask = FundingTask::get(FALSE)->execute()->single();
    static::assertSame($task->getId(), $fetchedTask['id']);
  }

}
