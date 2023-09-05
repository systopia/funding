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

namespace Civi\Funding\Api4\Action\Remote\FundingCase;

use Civi\Api4\RemoteFundingCase;
use Civi\PHPUnit\Traits\ArrayAssertTrait;

/**
 * @covers \Civi\Funding\Api4\Action\Remote\FundingCase\GetNewFormAction
 * @covers \Civi\Funding\FundingCase\Remote\Api4\ActionHandler\GetNewFormActionHandler
 * @covers \Civi\Api4\RemoteFundingCase
 *
 * @group headless
 */
final class GetNewFormActionTest extends AbstractNewFormActionTestCase {

  use ArrayAssertTrait;

  public function test(): void {
    $this->initFixtures();

    $action = RemoteFundingCase::getNewForm()
      ->setRemoteContactId($this->remoteContactId)
      ->setFundingProgramId($this->fundingProgram->getId())
      ->setFundingCaseTypeId($this->fundingCaseType->getId());

    $result = $action->execute();
    static::assertArrayHasSameKeys(['jsonSchema', 'uiSchema', 'data'], $result->getArrayCopy());
    static::assertIsArray($result['jsonSchema']);
    static::assertIsArray($result['uiSchema']);
    static::assertSame([], $result['data']);
  }

  public function testInvalidFundingProgramId(): void {
    $this->initFixtures();

    $action = RemoteFundingCase::getNewForm()
      ->setRemoteContactId($this->remoteContactId)
      ->setFundingProgramId($this->fundingProgram->getId() + 1)
      ->setFundingCaseTypeId($this->fundingCaseType->getId());

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage(sprintf(
      'Funding program with id "%d" not found',
      $this->fundingProgram->getId() + 1,
    ));
    $action->execute();
  }

  public function testInvalidFundingCaseTypeId(): void {
    $this->initFixtures();

    $action = RemoteFundingCase::getNewForm()
      ->setRemoteContactId($this->remoteContactId)
      ->setFundingProgramId($this->fundingProgram->getId())
      ->setFundingCaseTypeId($this->fundingCaseType->getId() + 1);

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage(sprintf(
      'Funding case type with id "%d" not found',
      $this->fundingCaseType->getId() + 1,
    ));
    $action->execute();
  }

}
