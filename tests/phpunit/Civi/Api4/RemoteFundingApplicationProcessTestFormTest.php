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

use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Fixtures\ApplicationProcessFixture;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Fixtures\FundingCaseFixture;
use Civi\Funding\Fixtures\FundingCaseTypeFixture;
use Civi\Funding\Fixtures\FundingCaseTypeProgramFixture;
use Civi\Funding\Fixtures\FundingProgramContactRelationFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;
use Civi\Funding\Mock\Form\FundingCaseType\TestJsonSchema;
use Civi\Funding\Mock\Form\FundingCaseType\TestJsonSchemaFactory;
use Civi\Funding\Mock\Form\FundingCaseType\TestUiSchema;

/**
 * @group headless
 *
 * @covers \Civi\Api4\RemoteFundingApplicationProcess
 */
final class RemoteFundingApplicationProcessTestFormTest extends AbstractFundingHeadlessTestCase {

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
      ->setData([
        'applicationProcessId' => $this->applicationProcess->getId(),
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

    $values = $action->execute()->getArrayCopy();
    static::assertEquals(['valid', 'errors'], array_keys($values));
    static::assertFalse($values['valid']);
    static::assertNotCount(0, $values['errors']);

    $validData = [
      'applicationProcessId' => $this->applicationProcess->getId(),
      'title' => 'My Title',
      'shortDescription' => 'My short description',
      'recipient' => $this->fundingCase->getRecipientContactId(),
      'startDate' => date('Y-m-d'),
      'endDate' => date('Y-m-d'),
      'amountRequested' => 123.45,
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
      ->setData([
        'applicationProcessId' => $this->applicationProcess->getId(),
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

    // Test with invalid data
    $values = $action->execute()->getArrayCopy();
    static::assertEquals(['action', 'message', 'errors'], array_keys($values));
    static::assertSame('showValidation', $values['action']);
    static::assertSame('Validation failed', $values['message']);
    static::assertNotCount(0, $values['errors']);

    // Test with valid data
    $validData = [
      'applicationProcessId' => $this->applicationProcess->getId(),
      'title' => 'My Title',
      'shortDescription' => 'My short description',
      'recipient' => $this->fundingCase->getRecipientContactId(),
      'startDate' => date('Y-m-d'),
      'endDate' => date('Y-m-d'),
      'amountRequested' => 123.45,
    ];
    $action->setData($validData + ['action' => 'save']);

    $values = $action->execute()->getArrayCopy();
    static::assertEquals(['action', 'message', 'jsonSchema', 'uiSchema', 'data'], array_keys($values));
    static::assertInstanceOf(TestJsonSchema::class, $values['jsonSchema']);
    static::assertInstanceOf(TestUiSchema::class, $values['uiSchema']);
    static::assertSame('showForm', $values['action']);
    static::assertEquals($validData, $values['data']);
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
      $this->contact['id']
    );

    $this->applicationProcess = ApplicationProcessFixture::addFixture(
      $this->fundingCase->getId(),
      [
        'start_date' => date('Y-m-d', time() - 86400),
        'end_date' => date('Y-m-d', time() + 86400),
        'request_data' => ['amountRequested' => 0],
      ]
    );
  }

}
