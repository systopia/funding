<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

namespace Civi\Api4;

use Civi\Funding\AbstractRemoteFundingHeadlessTestCase;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Fixtures\ApplicationProcessFixture;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\EntityFileFixture;
use Civi\Funding\Fixtures\ExternalFileFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Fixtures\FundingCaseFixture;
use Civi\Funding\Fixtures\FundingCaseTypeFixture;
use Civi\Funding\Fixtures\FundingCaseTypeProgramFixture;
use Civi\Funding\Fixtures\FundingProgramContactRelationFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;
use Civi\Funding\Mock\FundingCaseType\Application\JsonSchema\TestJsonSchema;
use Civi\Funding\Mock\FundingCaseType\Application\JsonSchema\TestJsonSchemaFactory;
use Civi\Funding\Mock\FundingCaseType\Application\UiSchema\TestUiSchema;

/**
 * @group headless
 *
 * @covers \Civi\Api4\RemoteFundingApplicationProcess
 */
final class RemoteFundingApplicationProcessTestFormTest extends AbstractRemoteFundingHeadlessTestCase {

  private ApplicationProcessEntity $applicationProcess;

  private FundingCaseEntity $fundingCase;

  private FundingCaseTypeEntity $fundingCaseType;

  private FundingProgramEntity $fundingProgram;

  /**
   * @phpstan-var array<string, mixed>&array{id: int}
   */
  private array $contact;

  protected function setUp(): void {
    parent::setUp();
    $this->addFixtures();
  }

  public function testGetForm(): void {
    $action = RemoteFundingApplicationProcess::getForm()
      ->setRemoteContactId((string) $this->contact['id'])
      ->setApplicationProcessId($this->applicationProcess->getId());

    $e = NULL;
    try {
      $action->execute();
    }
    catch (\Exception $e) {
      // @ignoreException
    }
    static::assertNotNull($e);
    static::assertSame(
      sprintf('Application process with ID "%d" not found', $this->applicationProcess->getId()),
      $e->getMessage()
    );

    FundingCaseContactRelationFixture::addContact(
      $this->contact['id'],
      $this->fundingCase->getId(),
      ['application_permission'],
    );

    $externalFile = ExternalFileFixture::addFixture([
      'identifier' => 'FundingApplicationProcess.' . $this->applicationProcess->getId() . ':file',
    ]);
    EntityFileFixture::addFixture(
      'civicrm_funding_application_process',
      $this->applicationProcess->getId(),
      $externalFile->getFileId(),
    );
    $this->clearCache();

    $values = $action->execute()->getArrayCopy();
    static::assertEquals(['jsonSchema', 'uiSchema', 'data'], array_keys($values));
    static::assertInstanceOf(TestJsonSchema::class, $values['jsonSchema']);
    static::assertInstanceOf(TestUiSchema::class, $values['uiSchema']);
    static::assertIsArray($values['data']);
    static::assertSame($this->applicationProcess->getTitle(), $values['data']['title']);
  }

  public function testValidateForm(): void {
    $action = RemoteFundingApplicationProcess::validateForm()
      ->setRemoteContactId((string) $this->contact['id'])
      ->setApplicationProcessId($this->applicationProcess->getId())
      ->setData([
        'y' => 'z',
      ]);

    $e = NULL;
    try {
      $action->execute();
    }
    catch (\Exception $e) {
      // @ignoreException
    }
    static::assertNotNull($e);
    static::assertSame(
      sprintf('Application process with ID "%d" not found', $this->applicationProcess->getId()),
      $e->getMessage()
    );

    FundingCaseContactRelationFixture::addContact(
      $this->contact['id'],
      $this->fundingCase->getId(),
      ['application_modify'],
    );
    $this->clearCache();

    $values = $action->execute()->getArrayCopy();
    static::assertEquals(['valid', 'errors'], array_keys($values));
    static::assertFalse($values['valid']);
    static::assertNotCount(0, $values['errors']);

    $validData = [
      'title' => 'My Title',
      'shortDescription' => 'My short description',
      'recipient' => $this->fundingCase->getRecipientContactId(),
      'startDate' => date('Y-m-d'),
      'endDate' => date('Y-m-d'),
      'amountRequested' => 123.45,
      'resources' => 12.34,
      'file' => 'https://example.org/test.txt',
    ];
    $action->setData($validData + ['action' => 'save']);

    $values = $action->execute()->getArrayCopy();
    static::assertEquals(['valid', 'errors'], array_keys($values));
    static::assertTrue($values['valid']);
    static::assertCount(0, $values['errors']);
  }

  public function testSubmitForm(): void {
    $action = RemoteFundingApplicationProcess::submitForm()
      ->setRemoteContactId((string) $this->contact['id'])
      ->setApplicationProcessId($this->applicationProcess->getId())
      ->setData([
        'y' => 'z',
      ]);

    $e = NULL;
    try {
      // Test without permission
      $action->execute();
    }
    catch (\Exception $e) {
      // @ignoreException
    }
    static::assertNotNull($e);
    static::assertSame(
      sprintf('Application process with ID "%d" not found', $this->applicationProcess->getId()),
      $e->getMessage()
    );

    FundingCaseContactRelationFixture::addContact(
      $this->contact['id'],
      $this->fundingCase->getId(),
      ['application_modify'],
    );
    $this->clearCache();

    // Test with invalid data
    $values = $action->execute()->getArrayCopy();
    static::assertEquals(['action', 'message', 'errors'], array_keys($values));
    static::assertSame('showValidation', $values['action']);
    static::assertSame('Validation failed', $values['message']);
    static::assertNotCount(0, $values['errors']);

    // Test with valid data
    $validData = [
      'title' => 'My Title',
      'shortDescription' => 'My short description',
      'recipient' => $this->fundingCase->getRecipientContactId(),
      'startDate' => date('Y-m-d'),
      'endDate' => date('Y-m-d'),
      'amountRequested' => 123.45,
      'resources' => 0,
      'file' => 'https://example.org/test.txt',
    ];
    $action->setData($validData + ['action' => 'save']);

    $values = $action->execute()->getArrayCopy();
    static::assertEquals(['action', 'message', 'files'], array_keys($values));
    static::assertSame('closeForm', $values['action']);
    $fileCiviUri = $values['files']['https://example.org/test.txt'];
    static::assertStringStartsWith('http://localhost/', $fileCiviUri);
  }

  private function addFixtures(): void {
    $this->fundingCaseType = FundingCaseTypeFixture::addFixture([
      'title' => 'Test',
      'name' => TestJsonSchemaFactory::getSupportedFundingCaseTypes()[0],
    ]);

    $this->fundingProgram = FundingProgramFixture::addFixture([
      'start_date' => date('Y-m-d', time() - 86400),
      'end_date' => date('Y-m-d', time() + 86400),
      'requests_start_date' => date('Y-m-d', time() - 86400),
      'requests_end_date' => date('Y-m-d', time() + 86400),
    ]);

    FundingCaseTypeProgramFixture::addFixture($this->fundingCaseType->getId(), $this->fundingProgram->getId());

    $this->contact = ContactFixture::addIndividual();

    FundingProgramContactRelationFixture::addContact(
      $this->contact['id'],
      $this->fundingProgram->getId(),
      ['application_create']
    );

    $this->fundingCase = FundingCaseFixture::addFixture(
      $this->fundingProgram->getId(),
      $this->fundingCaseType->getId(),
      $this->contact['id'],
      $this->contact['id'],
    );

    $this->applicationProcess = ApplicationProcessFixture::addFixture(
      $this->fundingCase->getId(),
      [
        'start_date' => date('Y-m-d', time() - 86400),
        'end_date' => date('Y-m-d', time() + 86400),
        'request_data' => [
          'amountRequested' => 0,
          'resources' => 0,
        ],
      ]
    );
  }

}
