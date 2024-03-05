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
use Civi\Funding\Entity\ApplicationCostItemEntity;
use Civi\Funding\Entity\ApplicationResourcesItemEntity;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\Fixtures\ApplicationCostItemFixture;
use Civi\Funding\Fixtures\ApplicationResourcesItemFixture;
use Civi\Funding\Fixtures\ClearingProcessBundleFixture;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\EntityFileFixture;
use Civi\Funding\Fixtures\ExternalFileFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Util\RequestTestUtil;

/**
 * @covers \Civi\Api4\FundingClearingProcess
 * @covers \Civi\Funding\Api4\Action\FundingClearingProcess\ValidateFormAction
 * @covers \Civi\Funding\ClearingProcess\Api4\ActionHandler\ValidateFormActionHandler
 *
 * @group headless
 */
final class ValidateFormActionTest extends AbstractFundingHeadlessTestCase {

  private ClearingProcessEntityBundle $clearingProcessBundle;

  private ApplicationCostItemEntity $costItem;

  private ApplicationResourcesItemEntity $resourcesItem;

  protected function setUp(): void {
    parent::setUp();

    $this->clearingProcessBundle = ClearingProcessBundleFixture::create(
      ['status' => 'review'],
      [
        'start_date' => '2024-03-04',
        'end_date' => '2024-03-05',
        'request_data' => ['amountRequested' => 10, 'resources' => 20],
      ]
    );
    $applicationProcessId = $this->clearingProcessBundle->getApplicationProcess()->getId();
    $this->costItem = ApplicationCostItemFixture::addFixture($applicationProcessId);
    $this->resourcesItem = ApplicationResourcesItemFixture::addFixture($applicationProcessId);

    $externalFile = ExternalFileFixture::addFixture([
      'identifier' => 'FundingApplicationProcess.' . $applicationProcessId . ':file',
    ]);
    EntityFileFixture::addFixture(
      'civicrm_funding_application_process',
      $applicationProcessId,
      $externalFile->getFileId(),
    );

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
