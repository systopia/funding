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

use Civi\Funding\SonstigeAktivitaet\AVK1Constants;

return [
  [
    'name' => 'FundingCaseType_AVK1SonstigeAktivitaet',
    'entity' => 'FundingCaseType',
    'cleanup' => 'never',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'title' => 'Sonstige Aktivität (AVK1)',
        'abbreviation' => 'SoA',
        'name' => AVK1Constants::FUNDING_CASE_TYPE_NAME,
        'is_combined_application' => FALSE,
        'application_process_label' => NULL,
        'properties' => NULL,
      ],
      'match' => [
        'name',
      ],
    ],
  ],
];
