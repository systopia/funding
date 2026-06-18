<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
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

use CRM_Funding_ExtensionUtil as E;

return [
  'funding_renderer_uri' => [
    'name' => 'funding_renderer_uri',
    'type' => 'String',
    'html_type' => 'select',
    'pseudoconstant' => ['callback' => '\Civi\Funding\Settings\RendererOptions::getOptions'],
    'default' => 'unoconv-local',
    'title' => E::ts('Funding Renderer URI'),
    'description' => E::ts('URI of the CiviOffice renderer to use.'),
    'settings_pages' => [
      'funding' => [
        'weight' => 20,
      ],
    ],
    'is_domain' => 1,
    'is_contact' => 0,
  ],
];
