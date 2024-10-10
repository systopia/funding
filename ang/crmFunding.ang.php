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
    // Ajv JSON schema validator, License: MIT https://ajv.js.org/
    // Source: https://cdnjs.cloudflare.com/ajax/libs/ajv/8.12.0/ajv7.min.js
    'ang/ajv7.min.js',
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
    'checklist-model',
    'lodash4',
    'ui.select',
    'skApplicationProcessTable',
    'skDrawdownTable',
  ],
  'settings' => [],
];
