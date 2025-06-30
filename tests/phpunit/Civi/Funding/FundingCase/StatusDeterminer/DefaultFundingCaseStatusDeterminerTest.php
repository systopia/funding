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

namespace Civi\Funding\FundingCase\StatusDeterminer;

use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\FundingCaseTypeFactory;
use Civi\Funding\FundingCase\Actions\FundingCaseActions;
use Civi\Funding\FundingCaseType\MetaData\ApplicationProcessStatus;
use Civi\Funding\Mock\FundingCaseType\MetaData\FundingCaseTypeMetaDataMock;
use Civi\Funding\Mock\FundingCaseType\MetaData\FundingCaseTypeMetaDataProviderMock;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Query\CompositeCondition;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingCase\StatusDeterminer\DefaultFundingCaseStatusDeterminer
 */
final class DefaultFundingCaseStatusDeterminerTest extends TestCase {

  private ApplicationProcessManager&MockObject $applicationProcessManagerMock;

  private DefaultFundingCaseStatusDeterminer $statusDeterminer;

  protected function setUp(): void {
    parent::setUp();
    $this->applicationProcessManagerMock = $this->createMock(ApplicationProcessManager::class);
    $metaDataMock = new FundingCaseTypeMetaDataMock(FundingCaseTypeFactory::DEFAULT_NAME);
    $this->statusDeterminer = new DefaultFundingCaseStatusDeterminer(
      $this->applicationProcessManagerMock,
      new FundingCaseTypeMetaDataProviderMock($metaDataMock),
    );

    $metaDataMock->applicationProcessStatuses = [
      'withdrawn' => new ApplicationProcessStatus([
        'name' => 'withdrawn',
        'label' => 'withdrawn',
        'eligible' => FALSE,
        'final' => TRUE,
        'withdrawn' => TRUE,
      ]),
      'rejected' => new ApplicationProcessStatus([
        'name' => 'rejected',
        'label' => 'rejected',
        'eligible' => FALSE,
        'final' => TRUE,
        'rejected' => TRUE,
      ]),
      'final_ineligible' => new ApplicationProcessStatus([
        'name' => 'final_ineligible',
        'label' => 'final_ineligible',
        'eligible' => FALSE,
        'final' => TRUE,
      ]),
      'eligible' => new ApplicationProcessStatus([
        'name' => 'eligible',
        'label' => 'eligible',
        'eligible' => TRUE,
        'final' => TRUE,
      ]),
      'ineligible_not_final' => new ApplicationProcessStatus([
        'name' => 'ineligible_not_final',
        'label' => 'ineligible_not_final',
        'eligible' => FALSE,
        'final' => FALSE,
      ]),
    ];
  }

  public function testGetStatus(): void {
    static::assertSame('test', $this->statusDeterminer->getStatus('test', 'do_something'));
    static::assertSame('ongoing', $this->statusDeterminer->getStatus('test', 'approve'));
    static::assertSame('cleared', $this->statusDeterminer->getStatus('test', FundingCaseActions::FINISH_CLEARING));
  }

  public function testGetStatusOnApplicationProcessStatusChangeWithdrawn(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'withdrawn',
      'is_withdrawn' => TRUE,
    ]);

    $this->applicationProcessManagerMock->method('countBy')
      ->with(CompositeCondition::new('AND',
        Comparison::new('funding_case_id', '=', $applicationProcessBundle->getFundingCase()->getId()),
        Comparison::new('status', 'NOT IN', ['withdrawn', 'rejected', 'final_ineligible']),
      ))->willReturn(0);

    static::assertSame(
      'withdrawn',
      $this->statusDeterminer->getStatusOnApplicationProcessStatusChange($applicationProcessBundle, 'previous')
    );
  }

  public function testGetStatusOnApplicationProcessStatusChangeRejected(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle([
      'status' => 'rejected',
      'is_rejected' => TRUE,
    ]);
    $this->applicationProcessManagerMock->method('countBy')
      ->with(CompositeCondition::new('AND',
        Comparison::new('funding_case_id', '=', $applicationProcessBundle->getFundingCase()->getId()),
        Comparison::new('status', 'NOT IN', ['withdrawn', 'rejected', 'final_ineligible']),
      ))->willReturn(0);

    static::assertSame(
      'rejected',
      $this->statusDeterminer->getStatusOnApplicationProcessStatusChange($applicationProcessBundle, 'previous')
    );
  }

  public function testGetStatusOnApplicationProcessStatusChangeWithRemainingApplications(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle(
      ['status' => 'withdrawn', 'is_withdrawn' => TRUE],
      ['status' => 'test']
    );
    $this->applicationProcessManagerMock->method('countBy')
      ->with(CompositeCondition::new('AND',
        Comparison::new('funding_case_id', '=', $applicationProcessBundle->getFundingCase()->getId()),
        Comparison::new('status', 'NOT IN', ['withdrawn', 'rejected', 'final_ineligible']),
      ))->willReturn(1);

    static::assertSame(
      'test',
      $this->statusDeterminer->getStatusOnApplicationProcessStatusChange($applicationProcessBundle, 'previous')
    );
  }

  public function testGetStatusOnApplicationProcessStatusChangeIneligibleNotFinalStatus(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle(
      ['status' => 'ineligible_not_final'],
      ['status' => 'test']
    );
    $this->applicationProcessManagerMock->expects(static::never())->method('countBy');

    static::assertSame(
      'test',
      $this->statusDeterminer->getStatusOnApplicationProcessStatusChange($applicationProcessBundle, 'previous')
    );
  }

}
