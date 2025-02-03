<?php
use CRM_Funding_ExtensionUtil as E;

return [
  'type' => 'search',
  'requires' => ['crmFunding'],
  'title' => E::ts('Funding Applications'),
  'icon' => 'fa-list-alt',
  'server_route' => 'civicrm/funding/application/list',
  'permission' => [
    'access Funding',
  ],
  'search_displays' => [
    'funding_case_application_processes.table',
  ],
];
