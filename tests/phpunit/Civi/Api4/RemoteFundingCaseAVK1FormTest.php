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
use Civi\Funding\Api4\Action\Remote\FundingCase\Traits\NewApplicationFormActionTrait;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingCaseTypeFixture;
use Civi\Funding\Fixtures\FundingCaseTypeProgramFixture;
use Civi\Funding\Fixtures\FundingProgramContactRelationFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;
use Civi\Funding\Form\SonstigeAktivitaet\JsonSchema\AVK1JsonSchema;
use Civi\Funding\Form\SonstigeAktivitaet\UISchema\AVK1UiSchema;
use Civi\Test;
use Civi\Test\CiviEnvBuilder;
use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;

/**
 * @group headless
 *
 * @covers \Civi\Api4\RemoteFundingCase
 */
final class RemoteFundingCaseAVK1FormTest extends TestCase implements HeadlessInterface, TransactionalInterface {

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

  public function setUpHeadless(): CiviEnvBuilder {
    return Test::headless()
      ->installMe(__DIR__)
      ->apply();
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
      ['some_permission']
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
      ['create_application']
    );

    $values = $action->execute()->getArrayCopy();
    static::assertEquals(['jsonSchema', 'uiSchema', 'data'], array_keys($values));
    static::assertInstanceOf(AVK1JsonSchema::class, $values['jsonSchema']);
    static::assertInstanceOf(AVK1UiSchema::class, $values['uiSchema']);
    static::assertIsArray($values['data']);
  }

  public function testValidateNewForm(): void {
    $action = RemoteFundingCase::validateNewApplicationForm()
      ->setRemoteContactId((string) $this->contact['id'])
      ->setData([
        'fundingProgramId' => $this->fundingProgram->getId(),
        'fundingCaseTypeId' => $this->fundingCaseType->getId(),
      ]);

    FundingProgramContactRelationFixture::addContact(
      $this->contact['id'],
      $this->fundingProgram->getId(),
      ['some_permission']
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
      ['create_application']
    );

    $values = $action->execute()->getArrayCopy();
    static::assertEquals(['valid', 'errors'], array_keys($values));
    static::assertFalse($values['valid']);
    static::assertNotCount(0, $values['errors']);
  }

  public function testSubmitNewForm(): void {
    $action = RemoteFundingCase::submitNewApplicationForm()
      ->setRemoteContactId((string) $this->contact['id'])
      ->setData([
        'fundingProgramId' => $this->fundingProgram->getId(),
        'fundingCaseTypeId' => $this->fundingCaseType->getId(),
      ]);

    FundingProgramContactRelationFixture::addContact(
      $this->contact['id'],
      $this->fundingProgram->getId(),
      ['some_permission']
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
      ['create_application']
    );

    $values = $action->execute()->getArrayCopy();
    static::assertEquals(['action', 'message', 'errors'], array_keys($values));
    static::assertSame('showValidation', $values['action']);
    static::assertSame('Validation failed', $values['message']);
    static::assertNotCount(0, $values['errors']);
  }

  private function addFixtures(): void {
    $this->fundingCaseType = FundingCaseTypeFixture::addFixture([
      'title' => 'AVK1 Test',
      'name' => 'AVK1SonstigeAktivitaet',
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
