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

use Civi\Funding\IJB\IJBConstants;

return [
  [
    'name' => 'FundingCaseType_IJB',
    'entity' => 'FundingCaseType',
    // "never" because the FundingCaseType entity itself is removed when this
    // extension is uninstalled.
    'cleanup' => 'never',
    'update' => 'unmodified',
    'params' => [
      'match' => ['name'],
      'version' => 4,
      'values' => [
        'title' => 'Internationale Jugendbegegnung',
        'abbreviation' => 'IJB',
        'name' => IJBConstants::FUNDING_CASE_TYPE_NAME,
        'is_combined_application' => FALSE,
        'application_process_label' => NULL,
        'properties' => NULL,
      ],
    ],
  ],
];
