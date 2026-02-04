<?php
use CRM_Funding_ExtensionUtil as E;

return [
  'type' => 'search',
  'title' => E::ts('Funding Form Translation'),
  'icon' => 'fa-list-alt',
  'server_route' => 'civicrm/funding/form-translation',
  'permission' => [
    'administer Funding',
  ],
  'search_displays' => [
    'funding_translation_form.table',
  ],
];
