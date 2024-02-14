<?php
use CRM_Funding_ExtensionUtil as E;

return [
  'type' => 'search',
  'title' => E::ts('Funding Programs'),
  'icon' => 'fa-list-alt',
  'server_route' => 'civicrm/funding/program/list',
  'permission' => [
    'access Funding',
  ],
  'search_displays' => [
    'funding_programs.table',
  ],
];
