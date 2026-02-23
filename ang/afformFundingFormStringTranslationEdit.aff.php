<?php
use CRM_Funding_ExtensionUtil as E;

return [
  'type' => 'form',
  'title' => E::ts('Edit String'),
  'icon' => 'fa-list-alt',
  'server_route' => 'civicrm/funding/funding/form-string-translation/edit',
  'permission' => [
    'administer Funding',
  ],
  'create_submission' => TRUE,
];
