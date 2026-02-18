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

namespace Civi\Funding\FundingCase\Approval;

use Civi\Funding\ApplicationProcess\EligibleApplicationProcessesLoader;
use Civi\Funding\EntityFactory\ApplicationProcessFactory;
use Civi\Funding\EntityFactory\FundingCaseBundleFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingCase\Approval\ApprovalValidator
 */
final class ApprovalValidatorTest extends TestCase {

  private ApprovalValidator $approvalValidator;

  private MockObject&EligibleApplicationProcessesLoader $eligibleApplicationProcessesLoaderMock;

  protected function setUp(): void {
    parent::setUp();
    $this->eligibleApplicationProcessesLoaderMock = $this->createMock(EligibleApplicationProcessesLoader::class);
    $this->approvalValidator = new ApprovalValidator($this->eligibleApplicationProcessesLoaderMock);
  }

  public function testIsAmountAllowedAdjustable(): void {
    $fundingCaseBundle = FundingCaseBundleFactory::create();

    $this->eligibleApplicationProcessesLoaderMock->expects(static::never())->method('getEligibleProcessesForContract');

    static::assertTrue($this->approvalValidator->isAmountAllowed(1.23, $fundingCaseBundle));
  }

  public function testIsAmountAllowedNonAdjustable(): void {
    $fundingCaseBundle = FundingCaseBundleFactory::create(
      fundingCaseTypeValues: ['properties' => ['amountApprovedNonAdjustable' => TRUE]]
    );

    $applicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'is_eligible' => TRUE,
      'amount_requested' => 1.23,
    ]);
    $this->eligibleApplicationProcessesLoaderMock->expects(static::once())->method('getEligibleProcessesForContract')
      ->willReturn([$applicationProcess]);

    static::assertTrue($this->approvalValidator->isAmountAllowed(1.23, $fundingCaseBundle));
  }

  public function testIsAmountAllowedNonAdjustableTooMuch(): void {
    $fundingCaseBundle = FundingCaseBundleFactory::create(
      fundingCaseTypeValues: ['properties' => ['amountApprovedNonAdjustable' => TRUE]]
    );

    $applicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'is_eligible' => TRUE,
      'amount_requested' => 1.23,
    ]);
    $this->eligibleApplicationProcessesLoaderMock->expects(static::once())->method('getEligibleProcessesForContract')
      ->willReturn([$applicationProcess]);

    static::assertFalse($this->approvalValidator->isAmountAllowed(1.24, $fundingCaseBundle));
  }

  public function testIsAmountAllowedNonAdjustableTooLess(): void {
    $fundingCaseBundle = FundingCaseBundleFactory::create(
      fundingCaseTypeValues: ['properties' => ['amountApprovedNonAdjustable' => TRUE]]
    );

    $applicationProcess = ApplicationProcessFactory::createApplicationProcess([
      'is_eligible' => TRUE,
      'amount_requested' => 1.23,
    ]);
    $this->eligibleApplicationProcessesLoaderMock->expects(static::once())->method('getEligibleProcessesForContract')
      ->willReturn([$applicationProcess]);

    static::assertFalse($this->approvalValidator->isAmountAllowed(1.22, $fundingCaseBundle));
  }

}
