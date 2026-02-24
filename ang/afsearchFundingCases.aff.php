<?php
use CRM_Funding_ExtensionUtil as E;

return [
  'type' => 'search',
  'requires' => ['crmFunding'],
  'title' => E::ts('Funding Cases'),
  'icon' => 'fa-list-alt',
  'server_route' => 'civicrm/funding/case/list',
  'permission' => [
    'access Funding',
  ],
  'search_displays' => [
    'funding_cases.table',
  ],
];
