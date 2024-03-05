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

namespace Civi\Funding\Api4\Action\FundingClearingProcess;

use Civi\Api4\FundingClearingProcess;
use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\ClearingProcess\ClearingProcessPermissions;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Fixtures\Traits\ClearingProcessFixturesTrait;
use Civi\Funding\Util\RequestTestUtil;

/**
 * @covers \Civi\Api4\FundingClearingProcess
 * @covers \Civi\Funding\Api4\Action\FundingClearingProcess\ValidateFormAction
 * @covers \Civi\Funding\ClearingProcess\Api4\ActionHandler\ValidateFormActionHandler
 *
 * @group headless
 */
final class ValidateFormActionTest extends AbstractFundingHeadlessTestCase {

  use ClearingProcessFixturesTrait;

  protected function setUp(): void {
    parent::setUp();

    $this->addFixtures(['status' => 'review']);
    $contact = ContactFixture::addIndividual();
    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $this->clearingProcessBundle->getFundingCase()->getId(),
      [ClearingProcessPermissions::REVIEW_CONTENT],
    );

    RequestTestUtil::mockInternalRequest($contact['id']);
  }

  public function testInvalid(): void {
    $result = FundingClearingProcess::validateForm()
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
    $result = FundingClearingProcess::validateForm()
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
        '_action' => 'update',
      ])
      ->execute()
      ->getArrayCopy();

    static::assertTrue($result['valid']);
    static::assertEquals(new \stdClass(), $result['errors']);
    static::assertIsArray($result['data']);
  }

}
