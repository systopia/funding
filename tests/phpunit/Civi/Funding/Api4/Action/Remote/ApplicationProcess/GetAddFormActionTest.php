<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\Funding\Api4\Action\Remote\ApplicationProcess;

use Civi\Api4\RemoteFundingApplicationProcess;
use Civi\Funding\Fixtures\ApplicationProcessFixture;
use Civi\PHPUnit\Traits\ArrayAssertTrait;

/**
 * @covers \Civi\Funding\Api4\Action\Remote\ApplicationProcess\GetAddFormAction
 * @covers \Civi\Funding\ApplicationProcess\Remote\Api4\ActionHandler\GetAddFormActionHandler
 * @covers \Civi\Api4\RemoteFundingApplicationProcess
 *
 * @group headless
 */
final class GetAddFormActionTest extends AbstractAddFormActionTestCase {

  use ArrayAssertTrait;

  public function test(): void {
    $this->initFixtures();

    $action = RemoteFundingApplicationProcess::getAddForm()
      ->setRemoteContactId($this->remoteContactId)
      ->setFundingCaseId($this->fundingCase->getId());

    $result = $action->execute();
    static::assertArrayHasSameKeys(['jsonSchema', 'uiSchema', 'data'], $result->getArrayCopy());
    static::assertIsArray($result['jsonSchema']);
    static::assertIsArray($result['uiSchema']);
    static::assertSame([], $result['data']);
  }

  public function testCopy(): void {
    $this->initFixtures();
    $applicationProcess = ApplicationProcessFixture::addFixtureForTestFundingCaseType($this->fundingCase->getId());

    $action = RemoteFundingApplicationProcess::getAddForm()
      ->setRemoteContactId($this->remoteContactId)
      ->setFundingCaseId($this->fundingCase->getId())
      ->setCopyDataFromId($applicationProcess->getId());

    $result = $action->execute();
    static::assertArrayHasSameKeys(['jsonSchema', 'uiSchema', 'data'], $result->getArrayCopy());
    static::assertIsArray($result['jsonSchema']);
    static::assertIsArray($result['uiSchema']);
    static::assertSame($applicationProcess->getTitle(), $result['data']['title']);
  }

  public function testInvalidFundingCaseId(): void {
    $this->initFixtures();

    $action = RemoteFundingApplicationProcess::getAddForm()
      ->setRemoteContactId($this->remoteContactId)
      ->setFundingCaseId($this->fundingCase->getId() + 1);

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage(sprintf('Funding case with id "%d" not found', $this->fundingCase->getId() + 1));
    $action->execute();
  }

}
