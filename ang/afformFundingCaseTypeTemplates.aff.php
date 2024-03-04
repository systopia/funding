<?php
use CRM_Funding_ExtensionUtil as E;

return [
  'type' => 'form',
  'title' => E::ts('Funding Case Type Templates'),
  'icon' => 'fa-list-alt',
  'server_route' => 'civicrm/funding/case-type/templates',
  'permission' => [
    'administer Funding',
  ],
  'create_submission' => TRUE,
];
