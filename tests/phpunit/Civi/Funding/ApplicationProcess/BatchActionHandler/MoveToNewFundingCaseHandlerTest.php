<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\ApplicationProcess\BatchActionHandler;

use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\Mock\RequestContext\TestRequestContext;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\BatchActionHandler\MoveToNewFundingCaseHandler
 */
final class MoveToNewFundingCaseHandlerTest extends TestCase {

  private MockObject&ApplicationProcessManager $applicationProcessManagerMock;

  private MockObject&FundingCaseManager $fundingCaseManagerMock;

  private MoveToNewFundingCaseHandler $handler;

  protected function setUp(): void {
    parent::setUp();
    $this->applicationProcessManagerMock = $this->createMock(ApplicationProcessManager::class);
    $this->fundingCaseManagerMock = $this->createMock(FundingCaseManager::class);
    $this->handler = new MoveToNewFundingCaseHandler(
      $this->applicationProcessManagerMock,
      $this->fundingCaseManagerMock,
      TestRequestContext::newInternal(123)
    );
  }

  public function testHandle(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::create(['id' => 12], fundingCaseValues: ['id' => 123]);
    $applicationProcess = $applicationProcessBundle->getApplicationProcess();
    $newFundingCase = FundingCaseFactory::createFundingCase(['id' => 1234]);

    $this->fundingCaseManagerMock->method('create')
      ->with(123, [
        'funding_program' => $applicationProcessBundle->getFundingProgram(),
        'funding_case_type' => $applicationProcessBundle->getFundingCaseType(),
        'recipient_contact_id' => $applicationProcessBundle->getFundingCase()->getRecipientContactId(),
        'notification_contact_ids' => $applicationProcessBundle->getFundingCase()->getNotificationContactIds(),
      ])
      ->willReturn($newFundingCase);

    $this->applicationProcessManagerMock->expects(static::once())->method('update')
      ->with(new ApplicationProcessEntityBundle(
        $applicationProcess,
        $newFundingCase,
        $applicationProcessBundle->getFundingCaseType(),
        $applicationProcessBundle->getFundingProgram()
      ));

    $result = $this->handler->handle([$applicationProcessBundle]);
    static::assertSame([$applicationProcess->getId() => $applicationProcess->toArray()], $result);
    static::assertSame($newFundingCase->getId(), $applicationProcess->getFundingCaseId());
  }

  public function testHandle_OneNewFundingCasePerFundingCase(): void {
    $applicationProcessBundle1 = ApplicationProcessBundleFactory::create(['id' => 1], fundingCaseValues: ['id' => 11]);
    $applicationProcessBundle2 = ApplicationProcessBundleFactory::create(['id' => 2], fundingCaseValues: ['id' => 22]);
    $applicationProcessBundle3 = ApplicationProcessBundleFactory::create(['id' => 3], fundingCaseValues: ['id' => 11]);
    $applicationProcess1 = $applicationProcessBundle1->getApplicationProcess();
    $applicationProcess2 = $applicationProcessBundle2->getApplicationProcess();
    $applicationProcess3 = $applicationProcessBundle3->getApplicationProcess();
    $newFundingCase1 = FundingCaseFactory::createFundingCase(['id' => 111]);
    $newFundingCase2 = FundingCaseFactory::createFundingCase(['id' => 222]);

    $createFundingCaseSeries = [
      [
        [
          123,
          [
            'funding_program' => $applicationProcessBundle1->getFundingProgram(),
            'funding_case_type' => $applicationProcessBundle1->getFundingCaseType(),
            'recipient_contact_id' => $applicationProcessBundle1->getFundingCase()->getRecipientContactId(),
            'notification_contact_ids' => $applicationProcessBundle1->getFundingCase()->getNotificationContactIds(),
          ],
        ],
        $newFundingCase1,
      ],
      [
        [
          123,
          [
            'funding_program' => $applicationProcessBundle2->getFundingProgram(),
            'funding_case_type' => $applicationProcessBundle2->getFundingCaseType(),
            'recipient_contact_id' => $applicationProcessBundle2->getFundingCase()->getRecipientContactId(),
            'notification_contact_ids' => $applicationProcessBundle2->getFundingCase()->getNotificationContactIds(),
          ],
        ],
        $newFundingCase2,
      ],
    ];
    $this->fundingCaseManagerMock->method('create')
      ->willReturnCallback(function (int $contactId, array $values) use (&$createFundingCaseSeries) {
        // @phpstan-ignore offsetAccess.nonArray
        [$expectedArgs, $return] = array_shift($createFundingCaseSeries);
        static::assertEquals($expectedArgs, [$contactId, $values]);

        return $return;
      });

    $applicationProcessUpdateSeries = [
      new ApplicationProcessEntityBundle(
        $applicationProcess1,
        $newFundingCase1,
        $applicationProcessBundle1->getFundingCaseType(),
        $applicationProcessBundle1->getFundingProgram()
      ),
      new ApplicationProcessEntityBundle(
        $applicationProcess2,
        $newFundingCase2,
        $applicationProcessBundle2->getFundingCaseType(),
        $applicationProcessBundle2->getFundingProgram()
      ),
      new ApplicationProcessEntityBundle(
        $applicationProcess3,
        $newFundingCase1,
        $applicationProcessBundle3->getFundingCaseType(),
        $applicationProcessBundle3->getFundingProgram()
      ),
    ];
    $this->applicationProcessManagerMock->expects(static::exactly(3))->method('update')
      ->willReturnCallback(
        function (ApplicationProcessEntityBundle $applicationProcessBundle) use (&$applicationProcessUpdateSeries) {
          $expected = array_shift($applicationProcessUpdateSeries);
          static::assertEquals($expected, $applicationProcessBundle);
        });

    $result = $this->handler->handle([
      $applicationProcessBundle1, $applicationProcessBundle2, $applicationProcessBundle3,
    ]);
    static::assertSame(
      [
        $applicationProcess1->getId() => $applicationProcess1->toArray(),
        $applicationProcess2->getId() => $applicationProcess2->toArray(),
        $applicationProcess3->getId() => $applicationProcess3->toArray(),
      ],
      $result
    );
    static::assertSame($newFundingCase1->getId(), $applicationProcess1->getFundingCaseId());
    static::assertSame($newFundingCase2->getId(), $applicationProcess2->getFundingCaseId());
    static::assertSame($newFundingCase1->getId(), $applicationProcess3->getFundingCaseId());
  }

}
