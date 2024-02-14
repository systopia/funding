<?php
use CRM_Funding_ExtensionUtil as E;

return [
  'type' => 'search',
  'title' => E::ts('My Tasks'),
  'icon' => 'fa-tasks',
  'server_route' => 'civicrm/funding/task/my/list',
  'permission' => [
    'access Funding',
  ],
  'search_displays' => [
    'funding_tasks_my.table',
  ],
];
