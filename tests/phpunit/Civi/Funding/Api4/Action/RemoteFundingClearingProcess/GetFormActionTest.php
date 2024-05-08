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

/**
 * @covers \Civi\Api4\RemoteFundingClearingProcess
 * @covers \Civi\Funding\Api4\Action\Remote\FundingClearingProcess\GetFormAction
 * @covers \Civi\Funding\ClearingProcess\Api4\ActionHandler\RemoteGetFormActionHandler
 *
 * @group headless
 */
final class GetFormActionTest extends AbstractRemoteFundingHeadlessTestCase {

  use ClearingProcessFixturesTrait;

  private string $remoteContactId;

  protected function setUp(): void {
    parent::setUp();

    $this->addFixtures(['status' => 'draft', 'report_data' => ['foo' => 'bar']]);
    $contact = ContactFixture::addIndividual();
    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $this->clearingProcessBundle->getFundingCase()->getId(),
      [ClearingProcessPermissions::CLEARING_MODIFY],
    );

    $this->remoteContactId = (string) $contact['id'];
  }

  public function test(): void {
    $result = RemoteFundingClearingProcess::getForm()
      ->setRemoteContactId($this->remoteContactId)
      ->setId($this->clearingProcessBundle->getClearingProcess()->getId())
      ->execute()
      ->getArrayCopy();

    static::assertArrayHasKey('jsonSchema', $result);
    static::assertArrayHasKey('uiSchema', $result);
    static::assertArrayHasKey('data', $result);

    static::assertIsArray($result['jsonSchema']['properties']['costItems']['properties'][$this->costItem->getId()]);
    static::assertIsArray(
      $result['jsonSchema']['properties']['resourcesItems']['properties'][$this->resourcesItem->getId()]
    );

    static::assertSame([
      'type' => 'object',
      'properties' => [
        'foo' => ['type' => 'string'],
        'file' => [
          'type' => 'string',
          'format' => 'uri',
          '$tag' => 'externalFile',
        ],
      ],
    ], $result['jsonSchema']['properties']['reportData']);

    // Test that scopes are parts of UI schema.
    $uiSchemaString = json_encode($result['uiSchema'], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    static::assertStringContainsString(
      '"#/properties/costItems/properties/' . $this->costItem->getId() . '/properties/',
      $uiSchemaString
    );
    static::assertStringContainsString(
      '"#/properties/resourcesItems/properties/' . $this->resourcesItem->getId() . '/properties/',
      $uiSchemaString
    );
    static::assertStringContainsString('"#/properties/reportData/properties/foo"', $uiSchemaString);

    static::assertEquals([
      'costItems' => new \stdClass(),
      'resourcesItems' => new \stdClass(),
      'costItemsAmountRecorded' => 0,
      'costItemsAmountAdmitted' => 0,
      'resourcesItemsAmountRecorded' => 0,
      'resourcesItemsAmountAdmitted' => 0,
      'amountCleared' => 0,
      'amountAdmitted' => 0,
      'reportData' => ['foo' => 'bar'],
    ], $result['data']);
  }

}
