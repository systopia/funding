<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

namespace Civi\Funding\Api4\Action\RemoteFundingClearingProcess;

use Civi\Api4\RemoteFundingClearingProcess;
use Civi\Funding\AbstractRemoteFundingHeadlessTestCase;
use Civi\Funding\ClearingProcess\ClearingProcessPermissions;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Fixtures\Traits\ClearingProcessFixturesTrait;
use Civi\Funding\Form\RemoteSubmitResponseActions;
use Civi\Funding\Util\RequestTestUtil;

/**
 * @covers \Civi\Api4\RemoteFundingClearingProcess
 * @covers \Civi\Funding\Api4\Action\Remote\FundingClearingProcess\SubmitFormAction
 * @covers \Civi\Funding\ClearingProcess\Api4\ActionHandler\RemoteSubmitFormActionHandler
 *
 * @group headless
 */
final class SubmitFormActionTest extends AbstractRemoteFundingHeadlessTestCase {

  use ClearingProcessFixturesTrait;

  private string $remoteContactId;

  protected function setUp(): void {
    parent::setUp();

    $this->addFixtures(['status' => 'draft']);
    $contact = ContactFixture::addIndividual();
    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $this->clearingProcessBundle->getFundingCase()->getId(),
      [ClearingProcessPermissions::CLEARING_MODIFY],
    );

    $this->remoteContactId = (string) $contact['id'];
    RequestTestUtil::mockRemoteRequest($this->remoteContactId);
  }

  public function testInvalid(): void {

    $result = RemoteFundingClearingProcess::submitForm()
      ->setRemoteContactId($this->remoteContactId)
      ->setId($this->clearingProcessBundle->getClearingProcess()->getId())
      ->setData([
        'costItems' => [
          $this->costItem->getId() => [
            'records' => [
              [
                'description' => 'costTest',
                'amount' => 'abc',
              ],
            ],
          ],
        ],
        'resourcesItems' => [
          $this->resourcesItem->getId() => [
            'records' => [
              [
                'description' => 'resourcesTest',
                'amount' => 'abc',
              ],
            ],
          ],
        ],
        'reportData' => ['foo' => 123],
        '_action' => 'invalid',
      ])
      ->execute()
      ->getArrayCopy();

    static::assertSame(RemoteSubmitResponseActions::SHOW_VALIDATION, $result['action']);
    static::assertSame('Validation failed', $result['message']);
    static::assertIsArray($result['errors']);
    static::assertCount(1, $result['errors']);
    static::assertEquals(new \stdClass(), $result['files']);
  }

  public function testValid(): void {
    $result = RemoteFundingClearingProcess::submitForm()
      ->setRemoteContactId($this->remoteContactId)
      ->setId($this->clearingProcessBundle->getClearingProcess()->getId())
      ->setData([
        'costItems' => [
          $this->costItem->getId() => [
            'records' => [
              [
                'description' => 'costTest',
                'amount' => 2,
              ],
            ],
          ],
        ],
        'resourcesItems' => [
          $this->resourcesItem->getId() => [
            'records' => [
              [
                'description' => 'resourcesTest',
                'amount' => 3,
              ],
            ],
          ],
        ],
        'reportData' => ['foo' => 'bar'],
        '_action' => 'save',
      ])
      ->execute()
      ->getArrayCopy();

    static::assertSame(RemoteSubmitResponseActions::CLOSE_FORM, $result['action']);
    static::assertSame('Saved', $result['message']);
    static::assertArrayNotHasKey('errors', $result);
    static::assertEquals(new \stdClass(), $result['files']);
  }

}
