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

use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\EntityFactory\FundingCaseBundleFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingCase\Approval\ApprovalValidator
 */
final class ApprovalValidatorTest extends TestCase {

  private ApplicationProcessManager&MockObject $applicationProcessManagerMock;

  private ApprovalValidator $approvalValidator;

  protected function setUp(): void {
    parent::setUp();
    $this->applicationProcessManagerMock = $this->createMock(ApplicationProcessManager::class);
    $this->approvalValidator = new ApprovalValidator($this->applicationProcessManagerMock);
  }

  public function testIsAmountAllowedAdjustable(): void {
    $fundingCaseBundle = FundingCaseBundleFactory::create();

    $this->applicationProcessManagerMock->expects(static::never())->method('getAmountEligibleByFundingCaseId');

    static::assertTrue($this->approvalValidator->isAmountAllowed(1.23, $fundingCaseBundle));
  }

  public function testIsAmountAllowedNonAdjustable(): void {
    $fundingCaseBundle = FundingCaseBundleFactory::create(
      fundingCaseTypeValues: ['properties' => ['amountApprovedNonAdjustable' => TRUE]]
    );

    $this->applicationProcessManagerMock
      ->expects(static::once())
      ->method('getAmountEligibleByFundingCaseId')
      ->with($fundingCaseBundle->getFundingCase()->getId())
      ->willReturn(1.23);

    static::assertTrue($this->approvalValidator->isAmountAllowed(1.23, $fundingCaseBundle));
  }

  public function testIsAmountAllowedNonAdjustableTooMuch(): void {
    $fundingCaseBundle = FundingCaseBundleFactory::create(
      fundingCaseTypeValues: ['properties' => ['amountApprovedNonAdjustable' => TRUE]]
    );

    $this->applicationProcessManagerMock
      ->expects(static::once())
      ->method('getAmountEligibleByFundingCaseId')
      ->with($fundingCaseBundle->getFundingCase()->getId())
      ->willReturn(1.23);

    static::assertFalse($this->approvalValidator->isAmountAllowed(1.24, $fundingCaseBundle));
  }

  public function testIsAmountAllowedNonAdjustableTooLess(): void {
    $fundingCaseBundle = FundingCaseBundleFactory::create(
      fundingCaseTypeValues: ['properties' => ['amountApprovedNonAdjustable' => TRUE]]
    );

    $this->applicationProcessManagerMock
      ->expects(static::once())
      ->method('getAmountEligibleByFundingCaseId')
      ->with($fundingCaseBundle->getFundingCase()->getId())
      ->willReturn(1.23);

    static::assertFalse($this->approvalValidator->isAmountAllowed(1.22, $fundingCaseBundle));
  }

}
