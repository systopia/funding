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

/**
 * @noinspection PhpUnhandledExceptionInspection
 */

declare(strict_types = 1);

namespace Civi\Api4;

use Civi\API\Exception\UnauthorizedException;
use Civi\Funding\AbstractRemoteFundingHeadlessTestCase;
use Civi\Funding\Api4\Action\Remote\FundingCase\Traits\NewApplicationFormActionTrait;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingCaseTypeFixture;
use Civi\Funding\Fixtures\FundingCaseTypeProgramFixture;
use Civi\Funding\Fixtures\FundingNewCasePermissionsFixture;
use Civi\Funding\Fixtures\FundingProgramContactRelationFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;
use Civi\Funding\Mock\Form\FundingCaseType\TestJsonSchema;
use Civi\Funding\Mock\Form\FundingCaseType\TestJsonSchemaFactory;
use Civi\Funding\Mock\Form\FundingCaseType\TestUiSchema;
use Symfony\Bridge\PhpUnit\ClockMock;

/**
 * @group headless
 *
 * @covers \Civi\Api4\RemoteFundingCase
 */
final class RemoteFundingCaseTestFormTest extends AbstractRemoteFundingHeadlessTestCase {

  private FundingCaseTypeEntity $fundingCaseType;

  private FundingProgramEntity $fundingProgram;

  /**
   * @phpstan-var array<string, mixed>&array{id: int}
   */
  private array $contact;

  public static function setUpBeforeClass(): void {
    parent::setUpBeforeClass();
    ClockMock::register(__CLASS__);
    ClockMock::register(NewApplicationFormActionTrait::class);
    ClockMock::withClockMock(strtotime('1970-01-02'));
  }

  protected function setUp(): void {
    parent::setUp();
    $this->addFixtures();
  }

  public function testGetNewForm(): void {
    $action = RemoteFundingCase::getNewApplicationForm()
      ->setRemoteContactId((string) $this->contact['id'])
      ->setFundingProgramId($this->fundingProgram->getId())
      ->setFundingCaseTypeId($this->fundingCaseType->getId());

    FundingProgramContactRelationFixture::addContact(
      $this->contact['id'],
      $this->fundingProgram->getId(),
      ['application_permission']
    );

    $e = NULL;
    try {
      $action->execute();
    }
    catch (UnauthorizedException $e) {
      // @ignoreException
    }
    static::assertNotNull($e);
    static::assertSame('Required permission is missing', $e->getMessage());

    FundingProgramContactRelationFixture::addContact(
      $this->contact['id'],
      $this->fundingProgram->getId(),
      ['application_create']
    );

    $values = $action->execute()->getArrayCopy();
    static::assertEquals(['jsonSchema', 'uiSchema', 'data'], array_keys($values));
    static::assertInstanceOf(TestJsonSchema::class, $values['jsonSchema']);
    static::assertInstanceOf(TestUiSchema::class, $values['uiSchema']);
    static::assertIsArray($values['data']);
  }

  public function testValidateNewForm(): void {
    $action = RemoteFundingCase::validateNewApplicationForm()
      ->setRemoteContactId((string) $this->contact['id'])
      ->setFundingProgramId($this->fundingProgram->getId())
      ->setFundingCaseTypeId($this->fundingCaseType->getId())
      ->setData(['foo' => 'bar']);

    FundingProgramContactRelationFixture::addContact(
      $this->contact['id'],
      $this->fundingProgram->getId(),
      ['application_permission']
    );

    $e = NULL;
    try {
      $action->execute();
    }
    catch (UnauthorizedException $e) {
      // @ignoreException
    }
    static::assertNotNull($e);
    static::assertSame('Required permission is missing', $e->getMessage());

    FundingProgramContactRelationFixture::addContact(
      $this->contact['id'],
      $this->fundingProgram->getId(),
      ['application_create']
    );

    $values = $action->execute()->getArrayCopy();
    static::assertEquals(['valid', 'errors'], array_keys($values));
    static::assertFalse($values['valid']);
    static::assertNotCount(0, $values['errors']);

    $validData = [
      'title' => 'My Title',
      'shortDescription' => 'My short description',
      'recipient' => $this->contact['id'],
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

  public function testSubmitNewForm(): void {
    $action = RemoteFundingCase::submitNewApplicationForm()
      ->setRemoteContactId((string) $this->contact['id'])
      ->setFundingProgramId($this->fundingProgram->getId())
      ->setFundingCaseTypeId($this->fundingCaseType->getId())
      ->setData(['foo' => 'bar']);

    FundingProgramContactRelationFixture::addContact(
      $this->contact['id'],
      $this->fundingProgram->getId(),
      ['application_permission']
    );

    FundingNewCasePermissionsFixture::addCreationContact($this->fundingProgram->getId(), ['application_permission']);

    $e = NULL;
    try {
      // Test without permission
      $action->execute();
    }
    catch (UnauthorizedException $e) {
      // @ignoreException
    }
    static::assertNotNull($e);
    static::assertSame('Required permission is missing', $e->getMessage());

    FundingProgramContactRelationFixture::addContact(
      $this->contact['id'],
      $this->fundingProgram->getId(),
      ['application_create']
    );

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
      'recipient' => $this->contact['id'],
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
  }

}
