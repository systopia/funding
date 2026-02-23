<?php
use CRM_Funding_ExtensionUtil as E;

return [
  'type' => 'search',
  'title' => E::ts('Strings'),
  'icon' => 'fa-list-alt',
  'server_route' => 'civicrm/funding/form-string-translation/list',
  'permission' => [
    'administer Funding',
  ],
  'search_displays' => [
    'funding_form_string_translation.translation',
  ],
];
