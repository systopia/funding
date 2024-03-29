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
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\Event\FundingCase\FundingCaseCreatedEvent;
use Civi\Funding\Event\FundingCase\FundingCaseUpdatedEvent;
use Civi\Funding\FileTypeNames;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Fixtures\FundingCaseFixture;
use Civi\Funding\Fixtures\FundingCaseTypeFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;
use Civi\Funding\FundingAttachmentManagerInterface;
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

  public static function setUpBeforeClass(): void {
    parent::setUpBeforeClass();
    ClockMock::register(__CLASS__);
    ClockMock::withClockMock(1234567);
  }

  protected function setUp(): void {
    parent::setUp();
    $this->attachmentManagerMock = $this->createMock(FundingAttachmentManagerInterface::class);
    $this->eventDispatcherMock = $this->createMock(CiviEventDispatcherInterface::class);
    $this->fundingCaseManager = new FundingCaseManager(
      new Api4(),
      $this->attachmentManagerMock,
      $this->eventDispatcherMock,
    );
  }

  public function testGetBy(): void {
    $api4Mock = $this->createMock(Api4Interface::class);
    $this->makeFullyMocked($api4Mock);

    $fundingCase = FundingCaseFactory::createFundingCase();

    $action = FundingCase::get(FALSE)
      ->setWhere([['title', '=', 'test']]);
    $api4Mock->expects(static::once())->method('executeAction')
      ->with($action)
      ->willReturn(new Result([$fundingCase->toArray()]));

    static::assertEquals(
      [$fundingCase],
      $this->fundingCaseManager->getBy(Comparison::new('title', '=', 'test'))
    );
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
      FundingCaseContactRelationFixture::addContact($event->getContactId(),
        $event->getFundingCase()->getId(),
        ['test_permission'],
      );
    });

    \CRM_Core_Session::singleton()->set('userID', $contact['id']);
    $fundingCase = $this->fundingCaseManager->create($contact['id'], [
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
      'amount_approved' => NULL,
      'transfer_contract_uri' => NULL,
      'permissions' => ['test_permission'],
      'PERM_test_permission' => TRUE,
    ],
      // Not given, but possible permissions are part of the flattened permissions
      TestUtil::filterFlattenedPermissions($fundingCase->toArray())
    );
  }

  public function testGetOpenOrCreate(): void {
    $contact = ContactFixture::addIndividual();
    $fundingProgram = FundingProgramFixture::addFixture();
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $recipientContact = ContactFixture::addOrganization();

    $this->eventDispatcherMock->expects(static::exactly(2))->method('dispatch')->with(
      FundingCaseCreatedEvent::class,
      static::isInstanceOf(FundingCaseCreatedEvent::class)
    )->willReturnCallback(function (string $eventName, FundingCaseCreatedEvent $event): void {
      FundingCaseContactRelationFixture::addContact($event->getContactId(),
        $event->getFundingCase()->getId(),
        ['test_permission'],
      );
    });

    RequestTestUtil::mockInternalRequest($contact['id']);
    $fundingCase = $this->fundingCaseManager->getOpenOrCreate($contact['id'], [
      'funding_program' => $fundingProgram,
      'funding_case_type' => $fundingCaseType,
      'recipient_contact_id' => $recipientContact['id'],
      'title' => 'Title',
    ]);

    static::assertSame('open', $fundingCase->getStatus());
    static::assertEquals($fundingCase, $this->fundingCaseManager->getOpenOrCreate($contact['id'], [
      'funding_program' => $fundingProgram,
      'funding_case_type' => $fundingCaseType,
      'recipient_contact_id' => $recipientContact['id'],
      'title' => 'Title',
    ]));

    $fundingCase->setStatus('test');
    FundingCase::update()->setValues($fundingCase->toArray())->execute();

    $fundingCase2 = $this->fundingCaseManager->getOpenOrCreate($contact['id'], [
      'funding_program' => $fundingProgram,
      'funding_case_type' => $fundingCaseType,
      'recipient_contact_id' => $recipientContact['id'],
      'title' => 'Title2',
    ]);
    static::assertNotSame($fundingCase->getId(), $fundingCase2->getId());
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

  public function testGet(): void {
    $fundingCase = $this->createFundingCase();

    $api4Mock = $this->createMock(Api4Interface::class);
    $this->makeFullyMocked($api4Mock);

    \CRM_Core_Session::singleton()->set('userID', 11);
    $api4Mock->expects(static::exactly(2))->method('executeAction')->withConsecutive(
      [
        static::callback(function (GetAction $action) {
          static::assertSame([['id', '=', 13, FALSE]], $action->getWhere());

          return TRUE;
        }),
      ],
      [
        static::callback(function (GetAction $action) {
          static::assertSame([['id', '=', 12, FALSE]], $action->getWhere());

          return TRUE;
        }),
      ]
    )->willReturnOnConsecutiveCalls(new Result(), new Result([$fundingCase->toArray()]));

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
    $contact = ContactFixture::addIndividual();
    $fundingCase = $this->createFundingCase();
    \CRM_Core_Session::singleton()->set('userID', $contact['id']);
    FundingCaseContactRelationFixture::addContact($contact['id'], $fundingCase->getId(), ['test_permission']);

    $updatedFundingCase = FundingCaseEntity::fromArray($fundingCase->toArray());
    $updatedFundingCase->setStatus('updated');

    $this->eventDispatcherMock->expects(static::once())->method('dispatch')
      ->with(FundingCaseUpdatedEvent::class, static::callback(
        function (FundingCaseUpdatedEvent $event) {
          static::assertSame('open', $event->getPreviousFundingCase()->getStatus());
          static::assertSame('updated', $event->getFundingCase()->getStatus());

          return TRUE;
        }
      ));

    sleep(1);
    $this->fundingCaseManager->update($updatedFundingCase);
    static::assertSame(time(), $updatedFundingCase->getModificationDate()->getTimestamp());
  }

  private function createFundingCase(): FundingCaseEntity {
    $fundingProgram = FundingProgramFixture::addFixture();
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $recipientContact = ContactFixture::addOrganization();
    $creationContact = ContactFixture::addIndividual(['first_name' => 'creation', 'last_name' => 'contact']);

    return FundingCaseFixture::addFixture(
      $fundingProgram->getId(),
      $fundingCaseType->getId(),
      $recipientContact['id'],
      $creationContact['id'],
    );
  }

  private function makeFullyMocked(Api4Interface $api4Mock): void {
    $this->fundingCaseManager = new FundingCaseManager(
      $api4Mock,
      $this->attachmentManagerMock,
      $this->eventDispatcherMock,
    );
  }

}
