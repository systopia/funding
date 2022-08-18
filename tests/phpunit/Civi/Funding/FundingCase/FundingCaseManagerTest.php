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

use Civi\Api4\FundingCaseContactRelation;
use Civi\Api4\Generic\Result;
use Civi\Core\CiviEventDispatcher;
use Civi\Funding\Api4\Action\FundingCase\GetAction;
use Civi\Funding\Event\FundingCase\FundingCaseCreatedEvent;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingCaseTypeFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;
use Civi\RemoteTools\Api4\Api4;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\Test;
use Civi\Test\CiviEnvBuilder;
use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;

/**
 * @covers \Civi\Funding\FundingCase\FundingCaseManager
 * @covers \Civi\Funding\Event\FundingCase\FundingCaseCreatedEvent
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
    )->willReturnCallback(function (string $eventName, FundingCaseCreatedEvent $event)
      use ($contact, $fundingProgram, $fundingCaseType): void {
      static::assertSame($contact['id'], $event->getContactId());
      static::assertSame($fundingProgram, $event->getFundingProgram());
      static::assertSame($fundingCaseType, $event->getFundingCaseType());
      FundingCaseContactRelation::create()
        ->setValues([
          'funding_case_id' => $event->getFundingCase()->getId(),
          'entity_table' => 'civicrm_contact',
          'entity_id' => $event->getContactId(),
          'permissions' => ['test_permission'],
        ])->execute();
    });

    $fundingCase = $this->fundingCaseManager->create($contact['id'], [
      'funding_program' => $fundingProgram,
      'funding_case_type' => $fundingCaseType,
      'recipient_contact_id' => $recipientContact['id'],
    ]);

    static::assertGreaterThan(0, $fundingCase->getId());
    static::assertEquals([
      'id' => $fundingCase->getId(),
      'funding_program_id' => $fundingProgram->getId(),
      'funding_case_type_id' => $fundingCaseType['id'],
      'recipient_contact_id' => $recipientContact['id'],
      'status' => 'open',
      'creation_date' => date('Y-m-d H:i:s'),
      'modification_date' => date('Y-m-d H:i:s'),
      'permissions' => ['test_permission'],
      'PERM_test_permission' => TRUE,
    ], $fundingCase->toArray());
  }

  public function testHasAccessTrue(): void {
    $api4Mock = $this->createMock(Api4Interface::class);
    $this->fundingCaseManager = new FundingCaseManager($api4Mock, $this->eventDispatcherMock);

    $api4Mock->expects(static::once())->method('executeAction')->with(static::callback(function (GetAction $action) {
      static::assertSame(11, $action->getContactId());
      static::assertSame([['id', '=', 12, FALSE]], $action->getWhere());

      return TRUE;
    }))->willReturn(new Result([['id' => 12]]));

    static::assertTrue($this->fundingCaseManager->hasAccess(11, 12));
  }

  public function testHasAccessFalse(): void {
    $api4Mock = $this->createMock(Api4Interface::class);
    $this->fundingCaseManager = new FundingCaseManager($api4Mock, $this->eventDispatcherMock);

    $api4Mock->expects(static::once())->method('executeAction')->with(static::callback(function (GetAction $action) {
      static::assertSame(11, $action->getContactId());
      static::assertSame([['id', '=', 12, FALSE]], $action->getWhere());

      return TRUE;
    }))->willReturn(new Result());

    static::assertFalse($this->fundingCaseManager->hasAccess(11, 12));
  }

}
