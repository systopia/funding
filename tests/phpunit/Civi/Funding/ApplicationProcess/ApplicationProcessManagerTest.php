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

namespace Civi\Funding\ApplicationProcess;

use Civi\Api4\FundingApplicationProcess;
use Civi\Api4\Generic\DAOGetAction;
use Civi\Core\CiviEventDispatcherInterface;
use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessCreatedEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreCreateEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreUpdateEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessUpdatedEvent;
use Civi\Funding\Fixtures\ApplicationProcessFixture;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Fixtures\FundingCaseFixture;
use Civi\Funding\Fixtures\FundingCaseTypeFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;
use Civi\Funding\Mock\Form\ValidatedApplicationDataMock;
use Civi\Funding\Util\RequestTestUtil;
use Civi\RemoteTools\Api4\Api4;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bridge\PhpUnit\ClockMock;

/**
 * @covers \Civi\Funding\ApplicationProcess\ApplicationProcessManager
 * @covers \Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreCreateEvent
 * @covers \Civi\Funding\Event\ApplicationProcess\ApplicationProcessCreatedEvent
 * @covers \Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreUpdateEvent
 * @covers \Civi\Funding\Event\ApplicationProcess\ApplicationProcessUpdatedEvent
 *
 * @group headless
 */
final class ApplicationProcessManagerTest extends AbstractFundingHeadlessTestCase {

  private ApplicationProcessManager $applicationProcessManager;

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
    $this->eventDispatcherMock = $this->createMock(CiviEventDispatcherInterface::class);
    $this->applicationProcessManager = new ApplicationProcessManager(
      new Api4(),
      $this->eventDispatcherMock
    );
  }

  public function testCountByFundingCaseId(): void {
    $contact = ContactFixture::addIndividual();
    $fundingCase = $this->createFundingCase();
    FundingCaseContactRelationFixture::addContact($contact['id'], $fundingCase->getId(), ['test_permission']);

    ApplicationProcessFixture::addFixture($fundingCase->getId());
    static::assertSame(0, $this->applicationProcessManager->countByFundingCaseId($fundingCase->getId()));

    RequestTestUtil::mockInternalRequest($contact['id']);
    static::assertSame(1, $this->applicationProcessManager->countByFundingCaseId($fundingCase->getId()));
  }

  public function testCreate(): void {
    $contact = ContactFixture::addIndividual();
    $fundingProgram = FundingProgramFixture::addFixture();
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $fundingCase = $this->createFundingCase($fundingProgram->getId(), $fundingCaseType->getId());

    $this->eventDispatcherMock->expects(static::exactly(2))->method('dispatch')->withConsecutive(
      [
        ApplicationProcessPreCreateEvent::class,
        static::callback(
          function (ApplicationProcessPreCreateEvent $event) use (
            $contact,
            $fundingCase,
            $fundingCaseType,
            $fundingProgram
          ) {
            static::assertSame($contact['id'], $event->getContactId());
            static::assertSame($fundingCase, $event->getFundingCase());
            static::assertSame($fundingCaseType, $event->getFundingCaseType());
            static::assertSame($fundingProgram, $event->getFundingProgram());

            return TRUE;
          }
        ),
      ],
      [
        ApplicationProcessCreatedEvent::class,
        static::callback(
          function (ApplicationProcessCreatedEvent $event) use (
            $contact,
            $fundingCase,
            $fundingCaseType,
            $fundingProgram
          ) {
            static::assertSame($contact['id'], $event->getContactId());
            static::assertSame($fundingCase, $event->getFundingCase());
            static::assertSame($fundingCaseType, $event->getFundingCaseType());
            static::assertSame($fundingProgram, $event->getFundingProgram());

            return TRUE;
          }
        ),
      ]
    );

    $validatedData = new ValidatedApplicationDataMock();
    $applicationProcess = $this->applicationProcessManager->create(
      $contact['id'], $fundingCase, $fundingCaseType, $fundingProgram, 'test_status', $validatedData
    );

    static::assertGreaterThan(0, $applicationProcess->getId());
    $applicationProcessValues = $applicationProcess->toArray();
    static::assertNotEmpty($applicationProcessValues['identifier']);
    unset($applicationProcessValues['identifier']);
    static::assertEquals([
      'id' => $applicationProcess->getId(),
      'funding_case_id' => $fundingCase->getId(),
      'status' => 'test_status',
      'title' => ValidatedApplicationDataMock::TITLE,
      'short_description' => ValidatedApplicationDataMock::SHORT_DESCRIPTION,
      'request_data' => ValidatedApplicationDataMock::APPLICATION_DATA,
      'amount_requested' => ValidatedApplicationDataMock::AMOUNT_REQUESTED,
      'creation_date' => date('Y-m-d H:i:s'),
      'modification_date' => date('Y-m-d H:i:s'),
      'start_date' => ValidatedApplicationDataMock::START_DATE,
      'end_date' => ValidatedApplicationDataMock::END_DATE,
      'is_review_content' => NULL,
      'reviewer_cont_contact_id' => NULL,
      'is_review_calculative' => NULL,
      'reviewer_calc_contact_id' => NULL,
      'is_eligible' => NULL,
    ], $applicationProcessValues);
  }

  public function testDelete(): void {
    $applicationProcessBundle = $this->createApplicationProcessBundle();
    // We need any permission so the get action automatically performed by CiviCRM will return the application process
    $contact = ContactFixture::addIndividual();
    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $applicationProcessBundle->getFundingCase()->getId(),
      ['test_permission']
    );

    RequestTestUtil::mockInternalRequest($contact['id']);
    $this->applicationProcessManager->delete($applicationProcessBundle);

    $action = (new DAOGetAction(FundingApplicationProcess::getEntityName(), 'delete'))
      ->addWhere('id', '=', $applicationProcessBundle->getApplicationProcess()->getId());
    static::assertCount(0, $action->execute());
  }

  public function testGet(): void {
    $contact = ContactFixture::addIndividual();
    $fundingCase = $this->createFundingCase();
    FundingCaseContactRelationFixture::addContact($contact['id'], $fundingCase->getId(), ['test_permission']);

    $applicationProcess = ApplicationProcessFixture::addFixture($fundingCase->getId());
    static::assertNull($this->applicationProcessManager->get($applicationProcess->getId()));

    RequestTestUtil::mockInternalRequest($contact['id']);
    static::assertNotNull($this->applicationProcessManager->get($applicationProcess->getId()));

    static::assertNull($this->applicationProcessManager->get($applicationProcess->getId() + 1));
  }

  public function testGetByFundingCaseId(): void {
    $contact = ContactFixture::addIndividual();
    RequestTestUtil::mockInternalRequest($contact['id']);
    $fundingCase1 = $this->createFundingCase();
    FundingCaseContactRelationFixture::addContact($contact['id'], $fundingCase1->getId(), ['test_permission']);
    $fundingCase2 = $this->createFundingCase(
      $fundingCase1->getFundingProgramId(),
      $fundingCase1->getFundingCaseTypeId(),
    );
    FundingCaseContactRelationFixture::addContact($contact['id'], $fundingCase2->getId(), ['test_permission']);

    $applicationProcess1 = ApplicationProcessFixture::addFixture(
      $fundingCase1->getId(),
      ['identifier' => 'test1', 'title' => 'Application1'],
    );
    $applicationProcess1->setValues($applicationProcess1->toArray());
    $applicationProcess2 = ApplicationProcessFixture::addFixture(
      $fundingCase2->getId(),
      ['identifier' => 'test2', 'title' => 'Application2'],
    );
    $applicationProcess2->setValues($applicationProcess2->toArray());

    static::assertEquals(
      [$applicationProcess1->getId() => $applicationProcess1],
      $this->applicationProcessManager->getByFundingCaseId($fundingCase1->getId())
    );
    static::assertEquals(
      [$applicationProcess2->getId() => $applicationProcess2],
      $this->applicationProcessManager->getByFundingCaseId($fundingCase2->getId())
    );
    static::assertSame([], $this->applicationProcessManager->getByFundingCaseId($fundingCase2->getId() + 1));
  }

  public function testGetFirstByFundingCaseId(): void {
    $contact = ContactFixture::addIndividual();
    $fundingCase = $this->createFundingCase();
    FundingCaseContactRelationFixture::addContact($contact['id'], $fundingCase->getId(), ['test_permission']);

    RequestTestUtil::mockInternalRequest($contact['id']);
    $firstApplicationProcess = $this->applicationProcessManager->getFirstByFundingCaseId($fundingCase->getId());
    static::assertNull($firstApplicationProcess);

    $applicationProcess = ApplicationProcessFixture::addFixture($fundingCase->getId());
    $firstApplicationProcess = $this->applicationProcessManager->getFirstByFundingCaseId($fundingCase->getId());
    static::assertNotNull($firstApplicationProcess);
    static::assertEquals($applicationProcess, $firstApplicationProcess);

    ApplicationProcessFixture::addFixture($fundingCase->getId(), ['title' => 'Title2', 'identifier' => 'test2']);
    $firstApplicationProcess = $this->applicationProcessManager->getFirstByFundingCaseId($fundingCase->getId());
    static::assertNotNull($firstApplicationProcess);
    static::assertEquals($applicationProcess->toArray(), $firstApplicationProcess->toArray());
  }

  public function testGetStatusListByFundingCaseId(): void {
    $contact = ContactFixture::addIndividual();
    $fundingCase = $this->createFundingCase();
    FundingCaseContactRelationFixture::addContact($contact['id'], $fundingCase->getId(), ['test_permission']);

    $applicationProcess1 = ApplicationProcessFixture::addFixture($fundingCase->getId());
    static::assertSame([], $this->applicationProcessManager->getStatusListByFundingCaseId($fundingCase->getId()));

    RequestTestUtil::mockInternalRequest($contact['id']);
    static::assertEquals(
      [$applicationProcess1->getId() => $applicationProcess1->getFullStatus()],
      $this->applicationProcessManager->getStatusListByFundingCaseId($fundingCase->getId())
    );

    $applicationProcess2 = ApplicationProcessFixture::addFixture($fundingCase->getId(), [
      'identifier' => 'test2',
      'status' => 'abc',
    ]);
    static::assertEquals(
      [
        $applicationProcess1->getId() => $applicationProcess1->getFullStatus(),
        $applicationProcess2->getId() => $applicationProcess2->getFullStatus(),
      ],
      $this->applicationProcessManager->getStatusListByFundingCaseId($fundingCase->getId())
    );

    static::assertSame([], $this->applicationProcessManager->getStatusListByFundingCaseId($fundingCase->getId() + 1));
  }

  public function testUpdate(): void {
    $contact = ContactFixture::addIndividual();
    $fundingProgram = FundingProgramFixture::addFixture();
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $fundingCase = $this->createFundingCase($fundingProgram->getId(), $fundingCaseType->getId());
    RequestTestUtil::mockInternalRequest($contact['id']);
    FundingCaseContactRelationFixture::addContact($contact['id'], $fundingCase->getId(), ['test_permission']);

    $applicationProcess = ApplicationProcessFixture::addFixture($fundingCase->getId());
    $applicationProcessBundle = new ApplicationProcessEntityBundle(
      $applicationProcess,
      $fundingCase,
      $fundingCaseType,
      $fundingProgram
    );
    $previousTitle = $applicationProcess->getTitle();

    $this->eventDispatcherMock->expects(static::exactly(2))->method('dispatch')->withConsecutive(
      [
        ApplicationProcessPreUpdateEvent::class,
        static::callback(
          function (ApplicationProcessPreUpdateEvent $event) use ($contact, $previousTitle, $applicationProcessBundle) {
            static::assertSame($contact['id'], $event->getContactId());
            static::assertSame($previousTitle, $event->getPreviousApplicationProcess()->getTitle());
            static::assertSame($applicationProcessBundle, $event->getApplicationProcessBundle());

            return TRUE;
          }
        ),
      ],
      [
        ApplicationProcessUpdatedEvent::class,
        static::callback(
          function (ApplicationProcessUpdatedEvent $event) use ($contact, $previousTitle, $applicationProcessBundle) {
            static::assertSame($contact['id'], $event->getContactId());
            static::assertSame($previousTitle, $event->getPreviousApplicationProcess()->getTitle());
            static::assertSame($applicationProcessBundle, $event->getApplicationProcessBundle());

            return TRUE;
          }
        ),
      ]
    );

    $applicationProcess->setTitle('New title');
    $this->applicationProcessManager->update($contact['id'], $applicationProcessBundle);
    static::assertSame(time(), $applicationProcess->getModificationDate()->getTimestamp());
  }

  private function createApplicationProcessBundle(): ApplicationProcessEntityBundle {
    $fundingProgram = FundingProgramFixture::addFixture();
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $fundingCase = $this->createFundingCase($fundingProgram->getId(), $fundingCaseType->getId());
    $applicationProcess = ApplicationProcessFixture::addFixture($fundingCase->getId());

    return new ApplicationProcessEntityBundle($applicationProcess, $fundingCase, $fundingCaseType, $fundingProgram);
  }

  private function createFundingCase(int $fundingProgramId = NULL, int $fundingCaseTypeId = NULL): FundingCaseEntity {
    $fundingProgramId ??= FundingProgramFixture::addFixture()->getId();
    $fundingCaseTypeId ??= FundingCaseTypeFixture::addFixture()->getId();
    $recipientContact = ContactFixture::addOrganization();
    $creationContact = ContactFixture::addIndividual(['first_name' => 'creation', 'last_name' => 'contact']);

    return FundingCaseFixture::addFixture(
      $fundingProgramId,
      $fundingCaseTypeId,
      $recipientContact['id'],
      $creationContact['id'],
    );
  }

}
