<?php
use CRM_Funding_ExtensionUtil as E;

return [
  'type' => 'form',
  'title' => E::ts('Edit Funding Program'),
  'icon' => 'fa-pencil',
  'server_route' => 'civicrm/funding/program/edit',
  'permission' => [
    'administer Funding',
  ],
  'redirect' => 'civicrm/funding/program/list',
  'create_submission' => TRUE,
];
