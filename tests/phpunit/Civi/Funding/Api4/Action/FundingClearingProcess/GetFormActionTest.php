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
 * @covers \Civi\Funding\Api4\Action\FundingClearingProcess\GetFormAction
 * @covers \Civi\Funding\ClearingProcess\Api4\ActionHandler\GetFormActionHandler
 *
 * @group headless
 */
final class GetFormActionTest extends AbstractFundingHeadlessTestCase {

  public function test(): void {
    $clearingProcessBundle = ClearingProcessBundleFixture::create(
      ['report_data' => ['foo' => 'bar']],
      [
        'start_date' => '2024-03-04',
        'end_date' => '2024-03-05',
        'request_data' => ['amountRequested' => 10, 'resources' => 20],
      ]
    );
    $applicationProcessId = $clearingProcessBundle->getApplicationProcess()->getId();
    $costItem = ApplicationCostItemFixture::addFixture($applicationProcessId);
    $resourcesItem = ApplicationResourcesItemFixture::addFixture($applicationProcessId);

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
      $clearingProcessBundle->getFundingCase()->getId(),
      ['review_test'],
    );

    RequestTestUtil::mockInternalRequest($contact['id']);

    $result = FundingClearingProcess::getForm()
      ->setId($clearingProcessBundle->getClearingProcess()->getId())
      ->execute()
      ->getArrayCopy();

    static::assertArrayHasKey('jsonSchema', $result);
    static::assertArrayHasKey('uiSchema', $result);
    static::assertArrayHasKey('data', $result);

    static::assertIsArray($result['jsonSchema']['properties']['costItems']['properties'][$costItem->getId()]);
    static::assertIsArray($result['jsonSchema']['properties']['resourcesItems']['properties'][$resourcesItem->getId()]);

    static::assertSame([
      'type' => 'object',
      'properties' => ['foo' => ['type' => 'string']],
    ], $result['jsonSchema']['properties']['reportData']);

    // Test that scopes are parts of UI schema.
    $uiSchemaString = json_encode($result['uiSchema'], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    static::assertStringContainsString(
      '"#/properties/costItems/properties/' . $costItem->getId() . '/properties/',
      $uiSchemaString
    );
    static::assertStringContainsString(
      '"#/properties/resourcesItems/properties/' . $resourcesItem->getId() . '/properties/',
      $uiSchemaString
    );
    static::assertStringContainsString('"#/properties/reportData/properties/foo"', $uiSchemaString);

    static::assertEquals([
      'costItems' => new \stdClass(),
      'resourcesItems' => new \stdClass(),
      'reportData' => ['foo' => 'bar'],
    ], $result['data']);
  }

}
