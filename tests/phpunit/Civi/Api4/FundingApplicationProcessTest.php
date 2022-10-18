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
use Civi\Funding\Fixtures\ApplicationProcessFixture;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Fixtures\FundingCaseFixture;
use Civi\Funding\Fixtures\FundingCaseTypeFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;
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
 *
 * @group headless
 */
final class FundingApplicationProcessTest extends TestCase implements HeadlessInterface, TransactionalInterface {

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

  private function createFundingCase(): FundingCaseEntity {
    $fundingProgram = FundingProgramFixture::addFixture();
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $recipientContact = ContactFixture::addOrganization();

    return FundingCaseFixture::addFixture(
      $fundingProgram->getId(),
      $fundingCaseType->getId(),
      $recipientContact['id'],
    );
  }

}
