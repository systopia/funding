<?php
// This file declares an Angular module which can be autoloaded
// in CiviCRM. See also:
// https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_angularModules
return [
  'js' => [
    'ang/crmFunding.js',
    'ang/crmFunding/*.js',
    'ang/crmFunding/*/*.js',
    'ang/crmFunding/*/*/*.js',
  ],
  'css' => [
    'ang/crmFunding.css',
  ],
  'partials' => [
    'ang/crmFunding',
  ],
  'requires' => [
    'crmUi',
    'crmUtil',
    'ngRoute',
    'xeditable',
    'lodash4',
  ],
  'settings' => [],
];
