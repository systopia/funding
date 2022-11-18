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

use Civi\API\Exception\UnauthorizedException;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Fixtures\ApplicationProcessFixture;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Fixtures\FundingCaseFixture;
use Civi\Funding\Fixtures\FundingCaseTypeFixture;
use Civi\Funding\Fixtures\FundingProgramContactRelationFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;
use Civi\Funding\Form\SonstigeAktivitaet\JsonSchema\AVK1JsonSchema;
use Civi\Funding\Util\SessionTestUtil;
use Civi\Test;
use Civi\Test\CiviEnvBuilder;
use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Api4\FundingApplicationProcess
 * @covers \Civi\Funding\Api4\Action\FundingApplicationProcess\DeleteAction
 * @covers \Civi\Funding\Api4\Action\FundingApplicationProcess\GetAction
 * @covers \Civi\Funding\Api4\Action\FundingApplicationProcess\GetFormDataAction
 * @covers \Civi\Funding\Api4\Action\FundingApplicationProcess\GetJsonSchemaAction
 *
 * @group headless
 */
final class FundingApplicationProcessTest extends TestCase implements HeadlessInterface, TransactionalInterface {

  /**
   * @phpstan-ignore-next-line
   */
  private FundingProgramEntity $fundingProgram;

  public function setUpHeadless(): CiviEnvBuilder {
    return Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function testDelete(): void {
    $contact = ContactFixture::addIndividual();
    $fundingCase = $this->createFundingCase();
    $applicationProcess = ApplicationProcessFixture::addFixture($fundingCase->getId());

    FundingCaseContactRelationFixture::addContact($contact['id'], $fundingCase->getId(), ['application_withdraw']);

    SessionTestUtil::mockRemoteRequestSession((string) $contact['id']);
    $result = FundingApplicationProcess::delete()->addWhere('id', '=', $applicationProcess->getId())->execute();
    static::assertCount(1, $result);
    static::assertSame(['id' => $applicationProcess->getId()], $result->first());
  }

  public function testDeleteMissingPermission(): void {
    $contact = ContactFixture::addIndividual();
    $fundingCase = $this->createFundingCase();
    $applicationProcess = ApplicationProcessFixture::addFixture($fundingCase->getId());

    FundingCaseContactRelationFixture::addContact($contact['id'], $fundingCase->getId(), ['application_permission']);

    SessionTestUtil::mockRemoteRequestSession((string) $contact['id']);
    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage('Deletion is not allowed');

    FundingApplicationProcess::delete()->addWhere('id', '=', $applicationProcess->getId())->execute();
  }

  public function testDeleteWithoutAnyPermission(): void {
    $contact = ContactFixture::addIndividual();
    $fundingCase = $this->createFundingCase();
    $applicationProcess = ApplicationProcessFixture::addFixture($fundingCase->getId());

    // Contact does not now that application process exists without any permission.
    SessionTestUtil::mockRemoteRequestSession((string) $contact['id']);
    $result = FundingApplicationProcess::delete()->addWhere('id', '=', $applicationProcess->getId())->execute();
    static::assertCount(0, $result);
  }

  public function testGet(): void {
    $contact = ContactFixture::addIndividual();
    $contactNotPermitted = ContactFixture::addIndividual();
    $fundingCase = $this->createFundingCase();
    $applicationProcess = ApplicationProcessFixture::addFixture($fundingCase->getId());

    FundingCaseContactRelationFixture::addContact($contact['id'], $fundingCase->getId(), ['application_permission']);

    SessionTestUtil::mockRemoteRequestSession((string) $contact['id']);
    $result = FundingApplicationProcess::get()->addSelect('id')->execute();
    static::assertCount(1, $result);
    static::assertSame(['id' => $applicationProcess->getId()], $result->first());

    SessionTestUtil::mockRemoteRequestSession((string) $contactNotPermitted['id']);
    static::assertCount(0, FundingApplicationProcess::get()
      ->addSelect('id')->execute());
  }

  public function testGetFormData(): void {
    $contact = ContactFixture::addIndividual();
    $fundingCase = $this->createFundingCase();
    $applicationProcess = ApplicationProcessFixture::addFixture($fundingCase->getId(), [
      'start_date' => '2022-11-15',
      'end_date' => '2022-11-16',
    ]);

    FundingProgramContactRelationFixture::addContact(
      $contact['id'],
      $this->fundingProgram->getId(),
      ['review_permission']
    );
    FundingCaseContactRelationFixture::addContact($contact['id'], $fundingCase->getId(), ['review_permission']);
    SessionTestUtil::mockInternalRequestSession($contact['id']);

    $result = FundingApplicationProcess::getFormData()
      ->setId($applicationProcess->getId())
      ->execute();

    static::assertIsArray($result['data']);
    static::assertSame('2022-11-15', $result['data']['beginn']);
  }

  public function testGetJsonSchema(): void {
    $contact = ContactFixture::addIndividual();
    $fundingCase = $this->createFundingCase();
    $applicationProcess = ApplicationProcessFixture::addFixture($fundingCase->getId());

    FundingProgramContactRelationFixture::addContact(
      $contact['id'],
      $this->fundingProgram->getId(),
      ['review_permission']
    );
    FundingCaseContactRelationFixture::addContact($contact['id'], $fundingCase->getId(), ['review_permission']);
    SessionTestUtil::mockInternalRequestSession($contact['id']);

    $result = FundingApplicationProcess::getJsonSchema()
      ->setId($applicationProcess->getId())
      ->execute();

    static::assertInstanceOf(AVK1JsonSchema::class, $result['jsonSchema']);
  }

  public function testSubmitForm(): void {
    $contact = ContactFixture::addIndividual();
    $fundingCase = $this->createFundingCase();
    $applicationProcess = ApplicationProcessFixture::addFixture($fundingCase->getId(), [
      'start_date' => '2022-11-15',
      'end_date' => '2022-11-16',
    ]);

    FundingProgramContactRelationFixture::addContact(
      $contact['id'],
      $this->fundingProgram->getId(),
      ['review_permission']
    );
    FundingCaseContactRelationFixture::addContact($contact['id'], $fundingCase->getId(), ['review_permission']);
    SessionTestUtil::mockInternalRequestSession($contact['id']);

    $result = FundingApplicationProcess::submitForm()
      ->setId($applicationProcess->getId())
      ->setData(['action' => 'test'])
      ->execute();

    static::assertIsArray($result['errors']);
    static::assertNotEmpty($result['errors']['/action']);
    static::assertNotEmpty($result['data']);
  }

  public function testValidateForm(): void {
    $contact = ContactFixture::addIndividual();
    $fundingCase = $this->createFundingCase();
    $applicationProcess = ApplicationProcessFixture::addFixture($fundingCase->getId(), [
      'start_date' => '2022-11-15',
      'end_date' => '2022-11-16',
    ]);

    FundingProgramContactRelationFixture::addContact(
      $contact['id'],
      $this->fundingProgram->getId(),
      ['review_permission']
    );
    FundingCaseContactRelationFixture::addContact($contact['id'], $fundingCase->getId(), ['review_permission']);
    SessionTestUtil::mockInternalRequestSession($contact['id']);

    $result = FundingApplicationProcess::validateForm()
      ->setId($applicationProcess->getId())
      ->setData(['action' => 'test'])
      ->execute();

    static::assertFalse($result['valid']);
    static::assertIsArray($result['errors']);
    static::assertNotEmpty($result['errors']['/action']);
    static::assertNotEmpty($result['data']);
  }

  private function createFundingCase(): FundingCaseEntity {
    $this->fundingProgram = FundingProgramFixture::addFixture();
    $fundingCaseType = FundingCaseTypeFixture::addFixture([
      'name' => 'AVK1SonstigeAktivitaet',
    ]);
    $recipientContact = ContactFixture::addOrganization();

    return FundingCaseFixture::addFixture(
      $this->fundingProgram->getId(),
      $fundingCaseType->getId(),
      $recipientContact['id'],
    );
  }

}
