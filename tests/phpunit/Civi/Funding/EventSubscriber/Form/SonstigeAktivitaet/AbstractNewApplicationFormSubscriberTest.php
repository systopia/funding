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

namespace Civi\Funding\EventSubscriber\Form\SonstigeAktivitaet;

use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Form\Validation\FormValidatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

abstract class AbstractNewApplicationFormSubscriberTest extends TestCase {

  /**
   * @var \Civi\Funding\Form\Validation\FormValidatorInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  protected MockObject $validatorMock;

  protected function setUp(): void {
    parent::setUp();
    $this->validatorMock = $this->createMock(FormValidatorInterface::class);
  }

  protected function createFundingCaseType(string $fundingCaseTypeName): FundingCaseTypeEntity {
    return FundingCaseTypeEntity::fromArray([
      'id' => 3,
      'title' => 'TestFundingCaseType',
      'name' => $fundingCaseTypeName,
      'properties' => [],
    ]);
  }

  protected function createFundingProgram(): FundingProgramEntity {
    return FundingProgramEntity::fromArray([
      'id' => 2,
      'title' => 'TestFundingProgram',
      'start_date' => '2022-10-22',
      'end_date' => '2023-10-22',
      'requests_start_date' => '2022-06-22',
      'requests_end_date' => '2022-12-31',
      'budget' => NULL,
      'currency' => '€',
    ]);
  }

}
