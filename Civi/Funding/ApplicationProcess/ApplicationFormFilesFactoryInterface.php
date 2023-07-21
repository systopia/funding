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

namespace Civi\Funding\ApplicationProcess;

interface ApplicationFormFilesFactoryInterface {

  public const SERVICE_TAG = 'funding.application.files_factory';

  /**
   * @phpstan-return array<string>
   */
  public static function getSupportedFundingCaseTypes(): array;

  /**
   * Adds identifiers to new files in request data, where necessary. Each file
   * needs an identifier.
   *
   * @param array<string, mixed> $requestData
   *
   * @return array<string, mixed>
   */
  public function addIdentifiers(array $requestData): array;

  /**
   * @phpstan-param array<string, mixed> $requestData
   *
   * @phpstan-return array<\Civi\Funding\Form\FundingFormFile>
   */
  public function createFormFiles(array $requestData): array;

}
