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

namespace Civi\Funding\FundingCase;

use Civi\Core\CiviEventDispatcher;
use Civi\Funding\Event\FundingCase\FundingCaseCreatedEvent;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingCaseTypeFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;
use Civi\RemoteTools\Api4\Api4;
use Civi\Test;
use Civi\Test\CiviEnvBuilder;
use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;

/**
 * @covers \Civi\Funding\FundingCase\FundingCaseManager
 *
 * @group headless
 */
final class FundingCaseManagerTest extends TestCase implements HeadlessInterface, TransactionalInterface {

  private FundingCaseManager $fundingCaseManager;

  /**
   * @var \Civi\Core\CiviEventDispatcher&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $eventDispatcherMock;

  public static function setUpBeforeClass(): void {
    parent::setUpBeforeClass();
    ClockMock::register(__CLASS__);
    ClockMock::withClockMock(1234567);
  }

  public function setUpHeadless(): CiviEnvBuilder {
    return Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  protected function setUp(): void {
    parent::setUp();
    $this->eventDispatcherMock = $this->createMock(CiviEventDispatcher::class);
    $this->fundingCaseManager = new FundingCaseManager(new Api4(), $this->eventDispatcherMock);
  }

  public function testCreate(): void {
    $contact = ContactFixture::addIndividual();
    $fundingProgram = FundingProgramFixture::addFixture();
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $recipientContact = ContactFixture::addOrganization();

    $this->eventDispatcherMock->expects(static::once())->method('dispatch')->with(
      FundingCaseCreatedEvent::class,
      static::isInstanceOf(FundingCaseCreatedEvent::class)
    );

    $fundingCase = $this->fundingCaseManager->create($contact['id'], [
      'funding_program' => $fundingProgram,
      'funding_case_type' => $fundingCaseType,
      'recipient_contact_id' => $recipientContact['id'],
    ]);

    static::assertGreaterThan(0, $fundingCase->getId());
    static::assertEquals([
      'id' => $fundingCase->getId(),
      'funding_program_id' => $fundingProgram['id'],
      'funding_case_type_id' => $fundingCaseType['id'],
      'recipient_contact_id' => $recipientContact['id'],
      'status' => 'open',
      'creation_date' => date('YmdHis'),
      'modification_date' => date('YmdHis'),
    ], $fundingCase->toArray());
  }

}
