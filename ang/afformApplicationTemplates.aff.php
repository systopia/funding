<?php
use CRM_Funding_ExtensionUtil as E;

return [
  'type' => 'form',
  'title' => E::ts('Application Templates'),
  'icon' => 'fa-file-text-o',
  'server_route' => 'civicrm/funding/application-templates',
  'permission' => [
    'administer Funding',
  ],
  'create_submission' => TRUE,
];
