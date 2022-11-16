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

namespace Civi\Funding\Fixtures;

use Civi\Api4\FundingApplicationProcess;
use Civi\Funding\Entity\ApplicationProcessEntity;

/**
 * @phpstan-type applicationProcessT array{
 *   id: int,
 *   identifier: string,
 *   funding_case_id: int,
 *   status: string,
 *   creation_date: string,
 *   modification_date: string,
 *   title: string,
 *   short_description: string,
 *   start_date: string|null,
 *   end_date: string|null,
 *   request_data: array<string, mixed>,
 *   amount_requested: float,
 *   amount_granted: float|null,
 *   granted_budget: float|null,
 *   is_review_content: bool|null,
 *   reviewer_cont_contact_id: int|null,
 *   is_review_calculative: bool|null,
 *   reviewer_calc_contact_id: int|null,
 * }
 */
final class ApplicationProcessFixture {

  /**
   * @phpstan-param array<string, mixed> $values
   *
   * @throws \API_Exception
   */
  public static function addFixture(int $fundingCaseId, array $values = []): ApplicationProcessEntity {
    $now = date('Y-m-d H:i:s');

    /** @phpstan-var applicationProcessT $applicationProcessValues */
    $applicationProcessValues = FundingApplicationProcess::create()
      ->setValues($values + [
        'identifier' => 'test',
        'funding_case_id' => $fundingCaseId,
        'status' => 'new',
        'title' => 'Title',
        'short_description' => 'Description',
        'request_data' => ['foo' => 'bar'],
        'amount_requested' => 1.2,
        'creation_date' => $now,
        'modification_date' => $now,
        'start_date' => NULL,
        'end_date' => NULL,
        'amount_granted' => NULL,
        'granted_budget' => NULL,
        'is_review_content' => NULL,
        'reviewer_cont_contact_id' => NULL,
        'is_review_calculative' => NULL,
        'reviewer_calc_contact_id' => NULL,
      ])->execute()->first();

    return ApplicationProcessEntity::fromArray($applicationProcessValues)->reformatDates();
  }

}
