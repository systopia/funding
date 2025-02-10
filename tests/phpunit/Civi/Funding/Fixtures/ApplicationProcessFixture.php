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
 * @phpstan-import-type applicationProcessT from ApplicationProcessEntity
 */
final class ApplicationProcessFixture {

  private static int $count = 0;

  /**
   * @phpstan-param array<string, mixed> $values
   *
   * @throws \CRM_Core_Exception
   */
  public static function addFixture(int $fundingCaseId, array $values = []): ApplicationProcessEntity {
    $now = date('Y-m-d H:i:s');

    $result = FundingApplicationProcess::create(FALSE)
      ->setValues($values + [
        'identifier' => 'test' . ++self::$count,
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
        'is_review_content' => NULL,
        'reviewer_cont_contact_id' => NULL,
        'is_review_calculative' => NULL,
        'reviewer_calc_contact_id' => NULL,
        'is_eligible' => NULL,
        'is_in_work' => TRUE,
        'is_rejected' => FALSE,
        'is_withdrawn' => FALSE,
      ])->execute();

    return ApplicationProcessEntity::singleFromApiResult($result)->reformatDates();
  }

  /**
   * @phpstan-param array<string, mixed> $values
   *
   * @throws \CRM_Core_Exception
   */
  public static function addFixtureForTestFundingCaseType(
    int $fundingCaseId,
    array $values = []
  ): ApplicationProcessEntity {
    $values += [
      'start_date' => '2023-04-05',
      'end_date' => '2023-04-06',
      'request_data' => ['resources' => 123.45],
    ];

    $applicationProcess = self::addFixture($fundingCaseId, $values);

    $externalFile = ExternalFileFixture::addFixture([
      'identifier' => 'FundingApplicationProcess.' . $applicationProcess->getId() . ':file',
    ]);
    EntityFileFixture::addFixture(
      'civicrm_funding_application_process',
      $applicationProcess->getId(),
      $externalFile->getFileId(),
    );

    return $applicationProcess;
  }

}
