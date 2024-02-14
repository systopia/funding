<?php
use CRM_Funding_ExtensionUtil as E;

return [
  'type' => 'form',
  'title' => E::ts('New Funding Program'),
  'icon' => 'fa-plus-circle',
  'server_route' => 'civicrm/funding/program/add',
  'permission' => [
    'administer Funding',
  ],
  'redirect' => 'civicrm/funding/program/list/#/?id=[FundingProgram1.0.id]',
  'create_submission' => TRUE,
];
