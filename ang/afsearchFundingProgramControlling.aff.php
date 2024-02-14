<?php
use CRM_Funding_ExtensionUtil as E;

return [
  'type' => 'search',
  'title' => E::ts('Funding Program Controlling'),
  'icon' => 'fa-list-alt',
  'server_route' => 'civicrm/funding/program/controlling',
  'permission' => [
    'access Funding',
    'administer Funding',
  ],
  'permission_operator' => 'OR',
  'search_displays' => [
    'funding_program_controlling.table',
  ],
];
