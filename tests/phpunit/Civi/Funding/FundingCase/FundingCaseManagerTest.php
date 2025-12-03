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

use Civi\Api4\FundingCase;
use Civi\Api4\Generic\DAODeleteAction;
use Civi\Api4\Generic\Result;
use Civi\Core\CiviEventDispatcherInterface;
use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\Api4\Action\FundingCase\GetAction;
use Civi\Funding\Entity\FundingCaseBundle;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\Funding\Event\FundingCase\FundingCaseCreatedEvent;
use Civi\Funding\Event\FundingCase\FundingCasePreCreateEvent;
use Civi\Funding\Event\FundingCase\FundingCasePreUpdateEvent;
use Civi\Funding\Event\FundingCase\FundingCaseUpdatedEvent;
use Civi\Funding\FileTypeNames;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingCaseBundleFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Fixtures\FundingCaseTypeFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;
use Civi\Funding\FundingAttachmentManagerInterface;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;
use Civi\Funding\FundingProgram\FundingProgramManager;
use Civi\Funding\Util\RequestTestUtil;
use Civi\Funding\Util\TestUtil;
use Civi\RemoteTools\Api4\Api4;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bridge\PhpUnit\ClockMock;

/**
 * @covers \Civi\Funding\FundingCase\FundingCaseManager
 * @covers \Civi\Funding\Event\FundingCase\FundingCaseCreatedEvent
 * @covers \Civi\Funding\Event\FundingCase\FundingCasePreCreateEvent
 *
 * @group headless
 */
final class FundingCaseManagerTest extends AbstractFundingHeadlessTestCase {

  /**
   * @var \Civi\Funding\FundingAttachmentManagerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $attachmentManagerMock;

  private FundingCaseManager $fundingCaseManager;

  /**
   * @var \Civi\Core\CiviEventDispatcherInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $eventDispatcherMock;

  /**
   * @var \Civi\Funding\FundingProgram\FundingCaseTypeManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fundingCaseTypeManagerMock;

  /**
   * @var \Civi\Funding\FundingProgram\FundingProgramManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fundingProgramManagerMock;

  public static function setUpBeforeClass(): void {
    parent::setUpBeforeClass();
    ClockMock::register(__CLASS__);
    ClockMock::withClockMock(1234567);
  }

  protected function setUp(): void {
    parent::setUp();
    $this->attachmentManagerMock = $this->createMock(FundingAttachmentManagerInterface::class);
    $this->eventDispatcherMock = $this->createMock(CiviEventDispatcherInterface::class);
    $this->fundingCaseTypeManagerMock = $this->createMock(FundingCaseTypeManager::class);
    $this->fundingProgramManagerMock = $this->createMock(FundingProgramManager::class);
    $this->fundingCaseManager = new FundingCaseManager(
      new Api4(),
      $this->attachmentManagerMock,
      $this->eventDispatcherMock,
      $this->fundingCaseTypeManagerMock,
      $this->fundingProgramManagerMock,
    );
  }

  public function testGetBundle(): void {
    $api4Mock = $this->createMock(Api4Interface::class);
    $this->makeFullyMocked($api4Mock);

    $fundingProgram = FundingProgramFactory::createFundingProgram();
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();
    $fundingCase = FundingCaseFactory::createFundingCase();

    $api4Mock->expects(static::once())->method('getEntities')
      ->with(FundingCase::getEntityName(), Comparison::new('id', '=', 12))
      ->willReturn(new Result([$fundingCase->toArray()]));

    $this->fundingCaseTypeManagerMock->expects(static::once())->method('get')
      ->with($fundingCase->getFundingCaseTypeId())
      ->willReturn($fundingCaseType);

    $this->fundingProgramManagerMock->expects(static::once())->method('get')
      ->with($fundingCase->getFundingProgramId())
      ->willReturn($fundingProgram);

    static::assertEquals(
      new FundingCaseBundle($fundingCase, $fundingCaseType, $fundingProgram),
      $this->fundingCaseManager->getBundle(12)
    );
  }

  public function testGetBundleBy(): void {
    $api4Mock = $this->createMock(Api4Interface::class);
    $this->makeFullyMocked($api4Mock);

    $fundingProgram = FundingProgramFactory::createFundingProgram();
    $fundingCaseType = FundingCaseTypeFactory::createFundingCaseType();
    $fundingCase = FundingCaseFactory::createFundingCase();

    $condition = Comparison::new('identifier', '=', 'test');
    $api4Mock->expects(static::once())->method('getEntities')
      ->with(FundingCase::getEntityName(), $condition)
      ->willReturn(new Result([$fundingCase->toArray()]));

    $this->fundingCaseTypeManagerMock->expects(static::once())->method('get')
      ->with($fundingCase->getFundingCaseTypeId())
      ->willReturn($fundingCaseType);

    $this->fundingProgramManagerMock->expects(static::once())->method('get')
      ->with($fundingCase->getFundingProgramId())
      ->willReturn($fundingProgram);

    static::assertEquals(
      [new FundingCaseBundle($fundingCase, $fundingCaseType, $fundingProgram)],
      $this->fundingCaseManager->getBundleBy($condition)
    );
  }

  public function testGetBy(): void {
    $api4Mock = $this->createMock(Api4Interface::class);
    $this->makeFullyMocked($api4Mock);

    $fundingCase = FundingCaseFactory::createFundingCase();

    $condition = Comparison::new('title', '=', 'test');
    $api4Mock->expects(static::once())->method('getEntities')
      ->with('FundingCase', $condition)
      ->willReturn(new Result([$fundingCase->toArray()]));

    static::assertEquals(
      [$fundingCase],
      $this->fundingCaseManager->getBy($condition)
    );
  }

  public function testCreate(): void {
    $contact = ContactFixture::addIndividual();
    $fundingProgram = FundingProgramFixture::addFixture();
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $recipientContact = ContactFixture::addOrganization();

    $this->eventDispatcherMock->expects(static::exactly(2))->method('dispatch')->willReturnCallback(
      function (string $eventName, $event) use ($contact, $fundingProgram, $fundingCaseType): void {
        static $calls = 0;
        if (1 === ++$calls) {
          static::assertSame(FundingCasePreCreateEvent::class, $eventName);
          static::assertInstanceOf(FundingCasePreCreateEvent::class, $event);
          /** @var \Civi\Funding\Event\FundingCase\FundingCasePreCreateEvent $event */
        }
        else {
          static::assertSame(FundingCaseCreatedEvent::class, $eventName);
          static::assertInstanceOf(FundingCaseCreatedEvent::class, $event);
          /** @var \Civi\Funding\Event\FundingCase\FundingCaseCreatedEvent $event */
          FundingCaseContactRelationFixture::addContact(
            $contact['id'],
            $event->getFundingCase()->getId(),
            ['test_permission'],
          );
        }

        static::assertSame($fundingProgram, $event->getFundingProgram());
        static::assertSame($fundingCaseType, $event->getFundingCaseType());
      }
    );

    \CRM_Core_Session::singleton()->set('userID', $contact['id']);
    $fundingCase = $this->fundingCaseManager->create(
      $contact['id'], [
        'funding_program' => $fundingProgram,
        'funding_case_type' => $fundingCaseType,
        'recipient_contact_id' => $recipientContact['id'],
        'title' => 'Title',
      ]);

    static::assertGreaterThan(0, $fundingCase->getId());
    static::assertEquals([
      'id' => $fundingCase->getId(),
      'identifier' => $fundingCase->getIdentifier(),
      'funding_program_id' => $fundingProgram->getId(),
      'funding_case_type_id' => $fundingCaseType->getId(),
      'recipient_contact_id' => $recipientContact['id'],
      'status' => 'open',
      'creation_date' => date('Y-m-d H:i:s'),
      'modification_date' => date('Y-m-d H:i:s'),
      'creation_contact_id' => $contact['id'],
      'notification_contact_ids' => [$contact['id']],
      'amount_approved' => NULL,
      'transfer_contract_uri' => NULL,
      'permissions' => ['test_permission'],
      'PERM_test_permission' => TRUE,
    ],
      // Not given, but possible permissions are part of the flattened permissions
      TestUtil::filterFlattenedPermissions($fundingCase->toArray())
    );
  }

  public function testGetOrCreate(): void {
    $contact = ContactFixture::addIndividual();
    $fundingProgram = FundingProgramFixture::addFixture();
    $fundingProgram2 = FundingProgramFixture::addFixture();
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $fundingCaseType2 = FundingCaseTypeFixture::addFixture([
      'title' => 'Test2',
      'abbreviation' => 't2',
      'name' => 'test2',
    ]);
    $recipientContact = ContactFixture::addOrganization();
    $recipientContact2 = ContactFixture::addOrganization();

    $this->eventDispatcherMock->expects(static::exactly(12))->method('dispatch')->willReturnCallback(
      function (string $eventName, $event) use ($contact): void {
        static $calls = 0;
        if (1 === (++$calls % 2)) {
          static::assertSame(FundingCasePreCreateEvent::class, $eventName);
          static::assertInstanceOf(FundingCasePreCreateEvent::class, $event);
          /** @var \Civi\Funding\Event\FundingCase\FundingCasePreCreateEvent $event */
        }
        else {
          static::assertSame(FundingCaseCreatedEvent::class, $eventName);
          static::assertInstanceOf(FundingCaseCreatedEvent::class, $event);
          /** @var \Civi\Funding\Event\FundingCase\FundingCaseCreatedEvent $event */
          FundingCaseContactRelationFixture::addContact(
            $contact['id'],
            $event->getFundingCase()->getId(),
            ['test_permission'],
          );
        }
      }
    );

    RequestTestUtil::mockInternalRequest($contact['id']);
    // Create a new funding case.
    $fundingCase = $this->fundingCaseManager->getOrCreate([], $contact['id'], [
      'funding_program' => $fundingProgram,
      'funding_case_type' => $fundingCaseType,
      'recipient_contact_id' => $recipientContact['id'],
      'title' => 'Title',
    ]);
    static::assertSame('open', $fundingCase->getStatus());

    // Test that the existing funding case is returned.
    $fundingCaseIdentical = $this->fundingCaseManager->getOrCreate(['open'], $contact['id'], [
      'funding_program' => $fundingProgram,
      'funding_case_type' => $fundingCaseType,
      'recipient_contact_id' => $recipientContact['id'],
      'title' => 'Title',
    ]);
    static::assertEquals($fundingCase, $fundingCaseIdentical);

    // Test that a new funding case is created when the allowed existing status ('foo') is different.
    $fundingCase2 = $this->fundingCaseManager->getOrCreate(['foo'], $contact['id'], [
      'funding_program' => $fundingProgram,
      'funding_case_type' => $fundingCaseType,
      'recipient_contact_id' => $recipientContact['id'],
      'title' => 'Title',
    ]);
    static::assertNotSame($fundingCase->getId(), $fundingCase2->getId());

    // Test that a new funding case is created because when allowed existing status are empty.
    $fundingCase3 = $this->fundingCaseManager->getOrCreate([], $contact['id'], [
      'funding_program' => $fundingProgram,
      'funding_case_type' => $fundingCaseType,
      'recipient_contact_id' => $recipientContact['id'],
      'title' => 'Title2',
    ]);
    static::assertNotSame($fundingCase->getId(), $fundingCase3->getId());

    // Test that a new funding case is created when the funding program is different.
    $fundingCase4 = $this->fundingCaseManager->getOrCreate(['open'], $contact['id'], [
      'funding_program' => $fundingProgram2,
      'funding_case_type' => $fundingCaseType,
      'recipient_contact_id' => $recipientContact['id'],
      'title' => 'Title',
    ]);
    static::assertNotSame($fundingCase->getId(), $fundingCase4->getId());

    // Test that a new funding case is created when the funding case type is different.
    $fundingCase5 = $this->fundingCaseManager->getOrCreate(['open'], $contact['id'], [
      'funding_program' => $fundingProgram,
      'funding_case_type' => $fundingCaseType2,
      'recipient_contact_id' => $recipientContact['id'],
      'title' => 'Title',
    ]);
    static::assertNotSame($fundingCase->getId(), $fundingCase4->getId());

    // Test that a new funding case is created when the recipient contact is different.
    $fundingCase6 = $this->fundingCaseManager->getOrCreate(['open'], $contact['id'], [
      'funding_program' => $fundingProgram,
      'funding_case_type' => $fundingCaseType,
      'recipient_contact_id' => $recipientContact2['id'],
      'title' => 'Title',
    ]);
    static::assertNotSame($fundingCase->getId(), $fundingCase6->getId());
  }

  public function testDelete(): void {
    $fundingCase = $this->createFundingCase();

    $api4Mock = $this->createMock(Api4Interface::class);
    $this->makeFullyMocked($api4Mock);

    $api4Mock->expects(static::once())->method('executeAction')->with(static::callback(
      function (DAODeleteAction $action) use ($fundingCase) {
        static::assertSame([['id', '=', $fundingCase->getId()]], $action->getWhere());

        return TRUE;
      }
    ))->willReturn(new Result([['id' => $fundingCase->getId()]]));

    $this->fundingCaseManager->delete($fundingCase);
  }

  public function testGetAmountRemaining(): void {
    $api4Mock = $this->createMock(Api4Interface::class);
    $this->makeFullyMocked($api4Mock);

    $api4Mock->method('execute')
      ->with(FundingCase::getEntityName(), 'get', [
        'select' => ['amount_admitted', 'amount_drawdowns_accepted'],
        'where' => [['id', '=', 23]],
      ])->willReturn(new Result([
        [
          'amount_admitted' => 25.2,
          'amount_drawdowns_accepted' => 14.1,
        ],
      ]));

    static::assertSame(11.1, $this->fundingCaseManager->getAmountRemaining(23));
  }

  public function testGet(): void {
    $fundingCase = $this->createFundingCase();

    $api4Mock = $this->createMock(Api4Interface::class);
    $this->makeFullyMocked($api4Mock);

    \CRM_Core_Session::singleton()->set('userID', 11);
    $series = [
      [
        [
          FundingCase::getEntityName(),
          Comparison::new('id', '=', 13),
        ],
        new Result(),
      ],
      [
        [
          FundingCase::getEntityName(),
          Comparison::new('id', '=', 12),
        ],
        new Result([$fundingCase->toArray()]),
      ],
    ];
    $api4Mock->expects(static::exactly(2))->method('getEntities')
      ->willReturnCallback(function ($entityName, $condition) use (&$series) {
        // @phpstan-ignore-next-line
        [$expectedArgs, $return] = array_shift($series);
        static::assertEquals($expectedArgs, [$entityName, $condition]);

        return $return;
      }
    );

    $fundingCaseLoaded = $this->fundingCaseManager->get(13);
    static::assertNull($fundingCaseLoaded);

    $fundingCaseLoaded = $this->fundingCaseManager->get(12);
    static::assertNotNull($fundingCaseLoaded);
    static::assertSame($fundingCase->toArray(), $fundingCaseLoaded->toArray());
  }

  public function testGetFirstBy(): void {
    $api4Mock = $this->createMock(Api4Interface::class);
    $this->makeFullyMocked($api4Mock);

    $fundingCase = FundingCaseFactory::createFundingCase();
    $condition = Comparison::new('funding_program_id', '=', $fundingCase->getFundingProgramId());

    $api4Mock->method('getEntities')
      ->with(FundingCase::getEntityName(), $condition, ['id' => 'ASC'], 1)
      ->willReturn(new Result([$fundingCase->toArray()]));

    static::assertEquals($fundingCase, $this->fundingCaseManager->getFirstBy($condition));
  }

  public function testGetFirstByNull(): void {
    $api4Mock = $this->createMock(Api4Interface::class);
    $this->makeFullyMocked($api4Mock);

    $condition = Comparison::new('funding_program_id', '=', 12);

    $api4Mock->method('getEntities')
      ->with(FundingCase::getEntityName(), $condition, ['id' => 'DESC'], 1)
      ->willReturn(new Result());

    static::assertNull($this->fundingCaseManager->getFirstBy($condition, ['id' => 'DESC']));
  }

  public function testGetAll(): void {
    $fundingCase = $this->createFundingCase();

    $api4Mock = $this->createMock(Api4Interface::class);
    $this->makeFullyMocked($api4Mock);

    \CRM_Core_Session::singleton()->set('userID', 11);
    $api4Mock->expects(static::once())->method('executeAction')->with(
      static::callback(function (GetAction $action) {
        static::assertSame([], $action->getWhere());

        return TRUE;
      })
    )->willReturn(new Result([$fundingCase->toArray()]));

    static::assertEquals([$fundingCase], $this->fundingCaseManager->getAll());
  }

  public function testHasAccessTrue(): void {
    $api4Mock = $this->createMock(Api4Interface::class);
    $this->makeFullyMocked($api4Mock);

    \CRM_Core_Session::singleton()->set('userID', 11);
    $api4Mock->expects(static::once())->method('executeAction')->with(static::callback(function (GetAction $action) {
      static::assertSame([['id', '=', 12, FALSE]], $action->getWhere());

      return TRUE;
    }))->willReturn(new Result([['id' => 12]]));

    static::assertTrue($this->fundingCaseManager->hasAccess(12));
  }

  public function testHasAccessFalse(): void {
    $api4Mock = $this->createMock(Api4Interface::class);
    $this->makeFullyMocked($api4Mock);

    \CRM_Core_Session::singleton()->set('userID', 11);
    $api4Mock->expects(static::once())->method('executeAction')->with(static::callback(function (GetAction $action) {
      static::assertSame([['id', '=', 12, FALSE]], $action->getWhere());

      return TRUE;
    }))->willReturn(new Result());

    static::assertFalse($this->fundingCaseManager->hasAccess(12));
  }

  public function testHasTransferContract(): void {
    $this->attachmentManagerMock->expects(static::once())->method('has')
      ->with('civicrm_funding_case', 12, FileTypeNames::TRANSFER_CONTRACT)
      ->willReturn(TRUE);

    static::assertTrue($this->fundingCaseManager->hasTransferContract(12));
  }

  public function testUpdate(): void {
    $fundingCaseBundle = FundingCaseBundleFixture::create();
    $fundingCase = $fundingCaseBundle->getFundingCase();
    $fundingCaseType = $fundingCaseBundle->getFundingCaseType();
    $fundingProgram = $fundingCaseBundle->getFundingProgram();

    $this->fundingCaseTypeManagerMock->method('get')->with($fundingCaseType->getId())
      ->willReturn($fundingCaseType);

    $this->fundingProgramManagerMock->method('get')->with($fundingCase->getFundingProgramId())
      ->willReturn($fundingProgram);

    $contact = ContactFixture::addIndividual();
    \CRM_Core_Session::singleton()->set('userID', $contact['id']);
    FundingCaseContactRelationFixture::addContact($contact['id'], $fundingCase->getId(), ['test_permission']);

    $updatedFundingCase = FundingCaseEntity::fromArray($fundingCase->toArray());
    $updatedFundingCase->setStatus('updated');

    // Reload to have permission fields required for the event object comparison.
    // We use the funding case manager so we also test that the previous case in
    // the events is actually fetched from the DB and not from cache.
    $fundingCase = $this->fundingCaseManager->get($fundingCase->getId());
    static::assertNotNull($fundingCase);
    $fundingCaseBundle = new FundingCaseBundle($fundingCase, $fundingCaseType, $fundingProgram);
    $previousFundingCase = clone $fundingCase;

    $fundingCase->setStatus('updated');

    $dispatchSeries = [
      [FundingCasePreUpdateEvent::class, new FundingCasePreUpdateEvent($previousFundingCase, $fundingCaseBundle)],
      [FundingCaseUpdatedEvent::class, new FundingCaseUpdatedEvent($previousFundingCase, $fundingCaseBundle)],
    ];
    $this->eventDispatcherMock->expects(static::exactly(2))->method('dispatch')
      ->willReturnCallback(function (...$args) use (&$dispatchSeries) {
        static::assertEquals(array_shift($dispatchSeries), $args);
      });

    sleep(1);
    $this->fundingCaseManager->update($fundingCase);
    static::assertSame(time(), $fundingCase->getModificationDate()->getTimestamp());
  }

  private function createFundingCase(): FundingCaseEntity {
    return FundingCaseBundleFixture::create()->getFundingCase();
  }

  private function makeFullyMocked(Api4Interface $api4Mock): void {
    $this->fundingCaseManager = new FundingCaseManager(
      $api4Mock,
      $this->attachmentManagerMock,
      $this->eventDispatcherMock,
      $this->fundingCaseTypeManagerMock,
      $this->fundingProgramManagerMock
    );
  }

}
