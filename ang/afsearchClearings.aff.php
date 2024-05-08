<?php
use CRM_Funding_ExtensionUtil as E;

return [
  'type' => 'search',
  'title' => E::ts('Clearings'),
  'icon' => 'fa-list-alt',
  'server_route' => 'civicrm/funding/clearing/list',
  'permission' => [
    'access Funding',
  ],
  'search_displays' => [
    'Clearings.Table',
  ],
];
