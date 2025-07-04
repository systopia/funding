<?php
use CRM_Funding_ExtensionUtil as E;

return [
  'type' => 'search',
  'title' => E::ts('Tasks (Pending)'),
  'icon' => 'fa-list-alt',
  'server_route' => 'civicrm/funding/task/list',
  'permission' => [
    'access CiviCRM',
    'access Funding',
  ],
  'search_displays' => [
    'Funding_Tasks_Pending.table',
  ],
];
