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

use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Fixtures\ApplicationProcessFixture;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Fixtures\FundingCaseFixture;
use Civi\Funding\Fixtures\FundingCaseTypeFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;
use Civi\Test;
use Civi\Test\CiviEnvBuilder;
use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Api4\FundingApplicationProcess
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

  public function testGet(): void {
    $contact = ContactFixture::addIndividual();
    $contactNotPermitted = ContactFixture::addIndividual();
    $fundingCase = $this->createFundingCase();
    $applicationProcess = ApplicationProcessFixture::addFixture($fundingCase->getId());

    FundingCaseContactRelationFixture::addContact($contact['id'], $fundingCase->getId(), ['test_permission']);

    \CRM_Core_Session::singleton()->set('userID', $contact['id']);
    $result = FundingApplicationProcess::get()->addSelect('id')->execute();
    static::assertCount(1, $result);
    static::assertSame(['id' => $applicationProcess->getId()], $result->first());

    \CRM_Core_Session::singleton()->set('userID', $contactNotPermitted['id']);
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
