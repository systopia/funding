<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\Funding\EventSubscriber\ApplicationProcess;

use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreCreateEvent;
use Civi\Funding\Event\ApplicationProcess\ApplicationProcessPreUpdateEvent;
use Civi\Funding\FundingCaseType\MetaData\ApplicationProcessStatus;
use Civi\Funding\Mock\FundingCaseType\MetaData\FundingCaseTypeMetaDataMock;
use Civi\Funding\Mock\FundingCaseType\MetaData\FundingCaseTypeMetaDataProviderMock;
use Civi\Funding\Mock\RequestContext\TestRequestContext;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\ApplicationProcess\ApplicationProcessStatusFlagsSubscriber
 */
final class ApplicationProcessStatusFlagsSubscriberTest extends TestCase {

  private FundingCaseTypeMetaDataMock $metaDataMock;

  private ApplicationProcessStatusFlagsSubscriber $subscriber;

  public static function setUpBeforeClass(): void {
    parent::setUpBeforeClass();
    putenv('TIME_FUNC=frozen');
  }

  protected function setUp(): void {
    parent::setUp();
    \CRM_Utils_Time::setTime('2026-01-02 03:04:05');
    $this->metaDataMock = new FundingCaseTypeMetaDataMock(FundingCaseTypeFactory::DEFAULT_NAME);
    $this->subscriber = new ApplicationProcessStatusFlagsSubscriber(
      new FundingCaseTypeMetaDataProviderMock($this->metaDataMock),
      TestRequestContext::newInternal(123)
    );
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      ApplicationProcessPreCreateEvent::class => ['onPreCreate', -100],
      ApplicationProcessPreUpdateEvent::class => ['onPreUpdate', -100],
    ];

    static::assertEquals($expectedSubscriptions, $this->subscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as [$method, $priority]) {
      static::assertTrue(method_exists(get_class($this->subscriber), $method));
    }
  }

  public function testOnPreCreate(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::create([
      'status' => 'test',
      'is_eligible' => NULL,
      'is_in_work' => TRUE,
      'is_withdrawn' => FALSE,
      'is_rejected' => TRUE,
    ]);

    $this->metaDataMock->applicationProcessStatuses['test'] = new ApplicationProcessStatus([
      'name' => 'test',
      'label' => 'Test',
      'eligible' => NULL,
      'final' => FALSE,
      'inReview' => FALSE,
      'inWork' => FALSE,
      'rejected' => TRUE,
      'withdrawn' => FALSE,
    ]);

    $event = new ApplicationProcessPreCreateEvent($applicationProcessBundle);
    $this->subscriber->onPreCreate($event);
    $applicationProcess = $applicationProcessBundle->getApplicationProcess();
    static::assertNull($applicationProcess->getIsEligible());
    static::assertFalse($applicationProcess->getIsInWork());
    static::assertTrue($applicationProcess->getIsRejected());
    static::assertFalse($applicationProcess->getIsWithdrawn());
    static::assertNull($applicationProcess->getFirstStatementOfEligibilityDate());
    static::assertNull($applicationProcess->getFirstStatementOfEligibilityContactId());
    static::assertNull($applicationProcess->getLastStatementOfEligibilityDate());
    static::assertNull($applicationProcess->getLastStatementOfEligibilityContactId());
  }

  public function testOnPreCreateEligible(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::create([
      'status' => 'test',
      'is_eligible' => NULL,
      'is_in_work' => TRUE,
      'is_withdrawn' => FALSE,
      'is_rejected' => TRUE,
    ]);

    $this->metaDataMock->applicationProcessStatuses['test'] = new ApplicationProcessStatus([
      'name' => 'test',
      'label' => 'Test',
      'eligible' => TRUE,
      'final' => FALSE,
      'inReview' => FALSE,
      'inWork' => FALSE,
      'rejected' => FALSE,
      'withdrawn' => FALSE,
    ]);

    $event = new ApplicationProcessPreCreateEvent($applicationProcessBundle);
    $this->subscriber->onPreCreate($event);
    $applicationProcess = $applicationProcessBundle->getApplicationProcess();
    static::assertTrue($applicationProcess->getIsEligible());
    static::assertFalse($applicationProcess->getIsInWork());
    static::assertFalse($applicationProcess->getIsRejected());
    static::assertFalse($applicationProcess->getIsWithdrawn());
    static::assertEquals(
      new \DateTime('2026-01-02 03:04:05'),
      $applicationProcess->getFirstStatementOfEligibilityDate()
    );
    static::assertSame(123, $applicationProcess->getFirstStatementOfEligibilityContactId());
    static::assertEquals(
      new \DateTime('2026-01-02 03:04:05'),
      $applicationProcess->getLastStatementOfEligibilityDate()
    );
    static::assertSame(123, $applicationProcess->getLastStatementOfEligibilityContactId());
  }

  public function testOnPreUpdate(): void {
    $previousApplicationProcessBundle = ApplicationProcessBundleFactory::create();
    $applicationProcessBundle = ApplicationProcessBundleFactory::create([
      'status' => 'test',
      'is_eligible' => TRUE,
      'first_statement_of_eligibility_date' => '2026-01-01 01:01:01',
      'first_statement_of_eligibility_contact_id' => 11,
      'last_statement_of_eligibility_date' => '2026-02-02 02:02:02',
      'last_statement_of_eligibility_contact_id' => 22,
    ]);

    $this->metaDataMock->applicationProcessStatuses['test'] = new ApplicationProcessStatus([
      'name' => 'test',
      'label' => 'Test',
      'eligible' => NULL,
      'final' => FALSE,
      'inReview' => FALSE,
      'inWork' => FALSE,
      'rejected' => TRUE,
      'withdrawn' => FALSE,
    ]);

    $event = new ApplicationProcessPreUpdateEvent($previousApplicationProcessBundle, $applicationProcessBundle);
    $this->subscriber->onPreUpdate($event);
    $applicationProcess = $applicationProcessBundle->getApplicationProcess();
    static::assertNull($applicationProcess->getIsEligible());
    static::assertEquals(
      new \DateTime('2026-01-01 01:01:01'),
      $applicationProcess->getFirstStatementOfEligibilityDate()
    );
    static::assertSame(11, $applicationProcess->getFirstStatementOfEligibilityContactId());
    static::assertEquals(
      new \DateTime('2026-02-02 02:02:02'),
      $applicationProcess->getLastStatementOfEligibilityDate()
    );
    static::assertSame(22, $applicationProcess->getLastStatementOfEligibilityContactId());
  }

  public function testOnPreUpdateFirstEligibility(): void {
    $previousApplicationProcessBundle = ApplicationProcessBundleFactory::create();
    $applicationProcessBundle = ApplicationProcessBundleFactory::create([
      'status' => 'test',
      'is_eligible' => FALSE,
    ]);

    $this->metaDataMock->applicationProcessStatuses['test'] = new ApplicationProcessStatus([
      'name' => 'test',
      'label' => 'Test',
      'eligible' => TRUE,
      'final' => FALSE,
      'inReview' => FALSE,
      'inWork' => FALSE,
      'rejected' => FALSE,
      'withdrawn' => FALSE,
    ]);

    $event = new ApplicationProcessPreUpdateEvent($previousApplicationProcessBundle, $applicationProcessBundle);
    $this->subscriber->onPreUpdate($event);
    $applicationProcess = $applicationProcessBundle->getApplicationProcess();
    static::assertTrue($applicationProcess->getIsEligible());
    static::assertEquals(
      new \DateTime('2026-01-02 03:04:05'),
      $applicationProcess->getFirstStatementOfEligibilityDate()
    );
    static::assertSame(123, $applicationProcess->getFirstStatementOfEligibilityContactId());
    static::assertEquals(
      new \DateTime('2026-01-02 03:04:05'),
      $applicationProcess->getLastStatementOfEligibilityDate()
    );
    static::assertSame(123, $applicationProcess->getLastStatementOfEligibilityContactId());
  }

  /**
   * Test that first statement of eligibility date and contact is not overridden.
   */
  public function testOnPreUpdateSecondEligibility(): void {
    $previousApplicationProcessBundle = ApplicationProcessBundleFactory::create();
    $applicationProcessBundle = ApplicationProcessBundleFactory::create([
      'status' => 'test',
      'is_eligible' => NULL,
      'first_statement_of_eligibility_date' => '2026-01-01 01:01:01',
      'first_statement_of_eligibility_contact_id' => 11,
      'last_statement_of_eligibility_date' => '2026-01-01 01:01:01',
      'last_statement_of_eligibility_contact_id' => 11,
    ]);

    $this->metaDataMock->applicationProcessStatuses['test'] = new ApplicationProcessStatus([
      'name' => 'test',
      'label' => 'Test',
      'eligible' => TRUE,
      'final' => FALSE,
      'inReview' => FALSE,
      'inWork' => FALSE,
      'rejected' => FALSE,
      'withdrawn' => FALSE,
    ]);

    $event = new ApplicationProcessPreUpdateEvent($previousApplicationProcessBundle, $applicationProcessBundle);
    $this->subscriber->onPreUpdate($event);
    $applicationProcess = $applicationProcessBundle->getApplicationProcess();
    static::assertTrue($applicationProcess->getIsEligible());
    static::assertEquals(
      new \DateTime('2026-01-01 01:01:01'),
      $applicationProcess->getFirstStatementOfEligibilityDate()
    );
    static::assertSame(11, $applicationProcess->getFirstStatementOfEligibilityContactId());
    static::assertEquals(
      new \DateTime('2026-01-02 03:04:05'),
      $applicationProcess->getLastStatementOfEligibilityDate()
    );
    static::assertSame(123, $applicationProcess->getLastStatementOfEligibilityContactId());
  }

}
