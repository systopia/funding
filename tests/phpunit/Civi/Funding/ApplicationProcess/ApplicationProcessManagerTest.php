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

use Civi\Core\CiviEventDispatcher;
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
use Civi\Funding\Util\TestUtil;
use Civi\RemoteTools\Api4\Api4;
use Civi\Test;
use Civi\Test\CiviEnvBuilder;
use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
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
final class ApplicationProcessManagerTest extends TestCase implements HeadlessInterface, TransactionalInterface {

  private ApplicationProcessManager $applicationProcessManager;

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
    $this->applicationProcessManager = new ApplicationProcessManager(
      new Api4(),
      $this->eventDispatcherMock
    );
  }

  public function testCreate(): void {
    $contact = ContactFixture::addIndividual();
    $fundingCase = $this->createFundingCase();

    $this->eventDispatcherMock->expects(static::exactly(2))->method('dispatch')->withConsecutive(
      [
        ApplicationProcessPreCreateEvent::class,
        static::callback(
          function (ApplicationProcessPreCreateEvent $event) use ($contact, $fundingCase) {
            static::assertSame($contact['id'], $event->getContactId());
            static::assertSame($fundingCase, $event->getFundingCase());

            return TRUE;
          }
        ),
      ],
      [
        ApplicationProcessCreatedEvent::class,
        static::callback(
          function (ApplicationProcessCreatedEvent $event) use ($contact, $fundingCase) {
            static::assertSame($contact['id'], $event->getContactId());
            static::assertSame($fundingCase, $event->getFundingCase());

            return TRUE;
          }
        ),
      ]
    );

    $applicationProcess = $this->applicationProcessManager->create($contact['id'], [
      'funding_case' => $fundingCase,
      'status' => 'new',
      'title' => 'Title',
      'short_description' => 'Description',
      'request_data' => ['foo' => 'bar'],
      'amount_requested' => 1.2,
    ]);

    static::assertGreaterThan(0, $applicationProcess->getId());
    static::assertEquals([
      'id' => $applicationProcess->getId(),
      'funding_case_id' => $fundingCase->getId(),
      'status' => 'new',
      'title' => 'Title',
      'short_description' => 'Description',
      'request_data' => ['foo' => 'bar'],
      'amount_requested' => 1.2,
      'creation_date' => date('Y-m-d H:i:s'),
      'modification_date' => date('Y-m-d H:i:s'),
      'start_date' => NULL,
      'end_date' => NULL,
      'amount_granted' => NULL,
      'granted_budget' => NULL,
      'is_review_content' => NULL,
      'is_review_calculative' => NULL,
    ], TestUtil::filterCiviExtraFields($applicationProcess->toArray()));
  }

  public function testGet(): void {
    $contact = ContactFixture::addIndividual();
    $fundingCase = $this->createFundingCase();
    FundingCaseContactRelationFixture::addContact($contact['id'], $fundingCase->getId(), ['test_permission']);

    $applicationProcess = ApplicationProcessFixture::addFixture($fundingCase->getId());
    static::assertNull($this->applicationProcessManager->get($applicationProcess->getId()));

    \CRM_Core_Session::singleton()->set('userID', $contact['id']);
    static::assertNotNull($this->applicationProcessManager->get($applicationProcess->getId()));

    static::assertNull($this->applicationProcessManager->get($applicationProcess->getId() + 1));
  }

  public function testGetFirstByFundingCaseId(): void {
    $contact = ContactFixture::addIndividual();
    $fundingCase = $this->createFundingCase();
    FundingCaseContactRelationFixture::addContact($contact['id'], $fundingCase->getId(), ['test_permission']);

    \CRM_Core_Session::singleton()->set('userID', $contact['id']);
    $firstApplicationProcess = $this->applicationProcessManager->getFirstByFundingCaseId($fundingCase->getId());
    static::assertNull($firstApplicationProcess);

    $applicationProcess = ApplicationProcessFixture::addFixture($fundingCase->getId());
    $firstApplicationProcess = $this->applicationProcessManager->getFirstByFundingCaseId($fundingCase->getId());
    static::assertNotNull($firstApplicationProcess);
    static::assertEquals(
      TestUtil::filterCiviExtraFields($applicationProcess->toArray()),
      $firstApplicationProcess->toArray(),
    );

    ApplicationProcessFixture::addFixture($fundingCase->getId(), ['title' => 'Title2']);
    $firstApplicationProcess = $this->applicationProcessManager->getFirstByFundingCaseId($fundingCase->getId());
    static::assertNotNull($firstApplicationProcess);
    static::assertEquals(
      TestUtil::filterCiviExtraFields($applicationProcess->toArray()),
      $firstApplicationProcess->toArray(),
    );
  }

  public function testUpdate(): void {
    $contact = ContactFixture::addIndividual();
    $fundingCase = $this->createFundingCase();
    \CRM_Core_Session::singleton()->set('userID', $contact['id']);
    FundingCaseContactRelationFixture::addContact($contact['id'], $fundingCase->getId(), ['test_permission']);

    $applicationProcess = ApplicationProcessFixture::addFixture($fundingCase->getId());
    $previousTitle = $applicationProcess->getTitle();

    $this->eventDispatcherMock->expects(static::exactly(2))->method('dispatch')->withConsecutive(
      [
        ApplicationProcessPreUpdateEvent::class,
        static::callback(
          function (ApplicationProcessPreUpdateEvent $event) use ($contact, $previousTitle,
            $applicationProcess, $fundingCase
          ) {
            static::assertSame($contact['id'], $event->getContactId());
            static::assertSame($previousTitle, $event->getPreviousApplicationProcess()->getTitle());
            static::assertSame($applicationProcess, $event->getApplicationProcess());
            static::assertSame($fundingCase, $event->getFundingCase());

            return TRUE;
          }
        ),
      ],
      [
        ApplicationProcessUpdatedEvent::class,
        static::callback(
          function (ApplicationProcessUpdatedEvent $event) use ($contact, $previousTitle,
          $applicationProcess, $fundingCase
          ) {
            static::assertSame($contact['id'], $event->getContactId());
            static::assertSame($previousTitle, $event->getPreviousApplicationProcess()->getTitle());
            static::assertSame($applicationProcess, $event->getApplicationProcess());
            static::assertSame($fundingCase, $event->getFundingCase());

            return TRUE;
          }
        ),
      ]
    );

    $applicationProcess->setTitle('New title');
    $this->applicationProcessManager->update($contact['id'], $applicationProcess, $fundingCase);
    static::assertSame(time(), $applicationProcess->getModificationDate()->getTimestamp());
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
