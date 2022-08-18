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

use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use PHPUnit\Framework\TestCase;

abstract class AbstractApplicationFormSubscriberTest extends TestCase {

  protected string $now;

  protected function setUp(): void {
    parent::setUp();
    $this->now = date('Y-m-d H:i:s');
  }

  protected function createApplicationProcess(): ApplicationProcessEntity {
    return ApplicationProcessEntity::fromArray([
      'id' => 2,
      'funding_case_id' => 3,
      'status' => 'new_status',
      'title' => 'Title',
      'short_description' => 'Description',
      'request_data' => ['foo' => 'bar'],
      'creation_date' => $this->now,
      'modification_date' => $this->now,
      'start_date' => NULL,
      'end_date' => NULL,
      'amount_granted' => NULL,
      'granted_budget' => NULL,
      'is_review_content' => NULL,
      'is_review_calculative' => NULL,
    ]);
  }

  protected function createFundingCase(): FundingCaseEntity {
    return FundingCaseEntity::fromArray([
      'funding_program_id' => 4,
      'funding_case_type_id' => 5,
      'recipient_contact_id' => 1,
      'status' => 'open',
      'creation_date' => $this->now,
      'modification_date' => $this->now,
      'permissions' => ['test_permission'],
    ]);
  }

  /**
   * @phpstan-return array<string, mixed>
   */
  protected function createFundingCaseType(string $fundingCaseTypeName): array {
    return ['id' => 5, 'name' => $fundingCaseTypeName];
  }

  protected function createFundingProgram(): FundingProgramEntity {
    return FundingProgramEntity::fromArray([
      'id' => 4,
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
