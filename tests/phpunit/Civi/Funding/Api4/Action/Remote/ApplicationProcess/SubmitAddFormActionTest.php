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
use Civi\Funding\Form\RemoteSubmitResponseActions;
use Civi\PHPUnit\Traits\ArrayAssertTrait;

/**
 * @covers \Civi\Funding\Api4\Action\Remote\ApplicationProcess\SubmitAddFormAction
 * @covers \Civi\Funding\ApplicationProcess\Remote\Api4\ActionHandler\SubmitAddFormActionHandler
 * @covers \Civi\Api4\RemoteFundingApplicationProcess
 *
 * @group headless
 */
final class SubmitAddFormActionTest extends AbstractAddFormActionTestCase {

  use ArrayAssertTrait;

  public function testSave(): void {
    $this->initFixtures();

    $data = [
      'title' => 'Title',
      'startDate' => '2023-08-07',
      'endDate' => '2023-08-08',
      'amountRequested' => 123.45,
      'resources' => 1.23,
      'file' => 'https://example.org/test.txt',
    ];
    $action = RemoteFundingApplicationProcess::submitAddForm()
      ->setRemoteContactId($this->remoteContactId)
      ->setFundingCaseId($this->fundingCase->getId())
      ->setData($data + ['_action' => 'save']);

    $result = $action->execute();
    static::assertArrayHasSameKeys(['action', 'message', 'files'], $result->getArrayCopy());
    static::assertSame(RemoteSubmitResponseActions::CLOSE_FORM, $result['action']);
    static::assertSame('Saved', $result['message']);
    static::assertIsArray($result['files']);
    static::assertArrayHasSameKeys(['https://example.org/test.txt'], $result['files']);
    static::assertIsString($result['files']['https://example.org/test.txt']);
    static::assertStringStartsWith('http://localhost/', $result['files']['https://example.org/test.txt']);
  }

  public function testSaveAndNew(): void {
    $this->initFixtures();

    $data = [
      'title' => 'Title',
      'startDate' => '2023-08-07',
      'endDate' => '2023-08-08',
      'amountRequested' => 123.45,
      'resources' => 1.23,
      'file' => 'https://example.org/test.txt',
    ];
    $action = RemoteFundingApplicationProcess::submitAddForm()
      ->setRemoteContactId($this->remoteContactId)
      ->setFundingCaseId($this->fundingCase->getId())
      ->setData($data + ['_action' => 'save&new']);

    $result = $action->execute();
    static::assertArrayHasSameKeys(['action', 'message', 'files'], $result->getArrayCopy());
    static::assertSame(RemoteSubmitResponseActions::RELOAD_FORM, $result['action']);
    static::assertSame('Saved', $result['message']);
    static::assertIsArray($result['files']);
    static::assertArrayHasSameKeys(['https://example.org/test.txt'], $result['files']);
    static::assertIsString($result['files']['https://example.org/test.txt']);
    static::assertStringStartsWith('http://localhost/', $result['files']['https://example.org/test.txt']);
  }

  public function testInvalidData(): void {
    $this->initFixtures();

    $action = RemoteFundingApplicationProcess::submitAddForm()
      ->setRemoteContactId($this->remoteContactId)
      ->setFundingCaseId($this->fundingCase->getId())
      ->setData(['foo' => 'bar']);

    $result = $action->execute();
    static::assertArrayHasSameKeys(['action', 'message', 'errors'], $result->getArrayCopy());
    static::assertSame('showValidation', $result['action']);
    static::assertSame('Validation failed', $result['message']);
    static::assertIsArray($result['errors']['/']);
  }

  public function testInvalidFundingCaseId(): void {
    $this->initFixtures();

    $action = RemoteFundingApplicationProcess::submitAddForm()
      ->setRemoteContactId($this->remoteContactId)
      ->setFundingCaseId($this->fundingCase->getId() + 1)
      ->setData(['foo' => 'bar']);

    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage(sprintf('Funding case with id "%d" not found', $this->fundingCase->getId() + 1));
    $action->execute();
  }

}
