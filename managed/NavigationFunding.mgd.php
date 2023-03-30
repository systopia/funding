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

use CRM_Funding_ExtensionUtil as E;

return [
  [
    'name' => 'Navigation_Funding',
    'entity' => 'Navigation',
    'cleanup' => 'always',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'domain_id' => 'current_domain',
        'label' => E::ts('Funding Program Manager'),
        'name' => 'funding',
        'url' => NULL,
        'icon' => NULL,
        'permission' => [
          'administer Funding',
          'access Funding',
        ],
        'permission_operator' => 'OR',
        'parent_id' => NULL,
        'is_active' => TRUE,
        'has_separator' => 0,
        'weight' => 111,
      ],
    ],
  ],
  [
    'name' => 'Navigation_Funding.FundingCases',
    'entity' => 'Navigation',
    'cleanup' => 'always',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'domain_id' => 'current_domain',
        'label' => E::ts('Funding Cases'),
        'name' => 'afsearchFundingCases',
        'url' => 'civicrm/funding/case/list',
        'icon' => 'crm-i fa-list-alt',
        'permission' => [
          'administer Funding',
          'access Funding',
        ],
        'permission_operator' => 'OR',
        'parent_id.name' => 'funding',
        'is_active' => TRUE,
        'has_separator' => 0,
      ],
    ],
  ],
];
