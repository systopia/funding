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
use Civi\Funding\Util\RequestTestUtil;

/**
 * @covers \Civi\Api4\RemoteFundingClearingProcess
 * @covers \Civi\Funding\Api4\Action\Remote\FundingClearingProcess\ValidateFormAction
 * @covers \Civi\Funding\ClearingProcess\Api4\ActionHandler\RemoteValidateFormActionHandler
 *
 * @group headless
 */
final class ValidateFormActionTest extends AbstractRemoteFundingHeadlessTestCase {

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
    $result = RemoteFundingClearingProcess::validateForm()
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

    static::assertFalse($result['valid']);
    static::assertCount(4, $result['errors']);
    static::assertIsArray($result['data']);
  }

  public function testValid(): void {
    $result = RemoteFundingClearingProcess::validateForm()
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

    static::assertTrue($result['valid']);
    static::assertEquals(new \stdClass(), $result['errors']);
    static::assertIsArray($result['data']);
  }

}
