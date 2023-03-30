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

namespace Civi\Funding\EntityFactory;

use Civi\Funding\Entity\ApplicationProcessEntityBundle;

/**
 * @phpstan-type applicationProcessValuesT array{
 *   id?: int|null,
 *   funding_case_id?: int,
 *   status?: string,
 *   creation_date?: string,
 *   modification_date?: string,
 *   title?: string,
 *   short_description?: string,
 *   start_date?: string|null,
 *   end_date?: string|null,
 *   request_data?: array<string, mixed>,
 *   amount_requested?: float,
 *   is_review_content?: bool|null,
 *   reviewer_cont_contact_id?: int|null,
 *   is_review_calculative?: bool|null,
 *   reviewer_calc_contact_id?: int|null,
 *   is_eligible?: bool|null,
 *   check_permissions?: bool,
 *   custom?: mixed,
 * }
 *
 * @phpstan-type fundingCaseValuesT array{
 *   id?: int,
 *   funding_program_id?: int,
 *   funding_case_type_id?: int,
 *   status?: string,
 *   title?: string,
 *   recipient_contact_id?: int,
 *   creation_date?: string,
 *   modification_date?: string,
 *   amount_approved?: float|null,
 *   permissions?: array<string>,
 *   transfer_contract_uri?: string|null,
 * }
 *
 * @phpstan-type fundingCaseTypeValuesT array{
 *   id?: int,
 *   title?: string,
 *   name?: string,
 *   properties?: array<string, mixed>,
 * }
 *
 * @phpstan-type fundingProgramValuesT array{
 *   id?: int,
 *   title?: string,
 *   start_date?: string,
 *   end_date?: string,
 *   requests_start_date?: string,
 *   requests_end_date?: string,
 *   currency?: string,
 *   budget?: float|null,
 *   permissions?: array<string>,
 * }
 */
final class ApplicationProcessBundleFactory {

  /**
   * @phpstan-param applicationProcessValuesT $applicationProcessValues
   * @phpstan-param fundingCaseValuesT $fundingCaseValues
   * @phpstan-param fundingCaseTypeValuesT $fundingCaseTypeValues
   * @phpstan-param fundingProgramValuesT $fundingProgramValues
   */
  public static function createApplicationProcessBundle(
    array $applicationProcessValues = [],
    array $fundingCaseValues = [],
    array $fundingCaseTypeValues = [],
    array $fundingProgramValues = []
  ): ApplicationProcessEntityBundle {
    return new ApplicationProcessEntityBundle(
      ApplicationProcessFactory::createApplicationProcess($applicationProcessValues),
      FundingCaseFactory::createFundingCase($fundingCaseValues),
      FundingCaseTypeFactory::createFundingCaseType($fundingCaseTypeValues),
      FundingProgramFactory::createFundingProgram($fundingProgramValues),
    );
  }

}
