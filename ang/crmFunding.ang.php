<?php
// This file declares an Angular module which can be autoloaded
// in CiviCRM. See also:
// https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_angularModules

use Civi\Api4\FundingCaseType;

$module = [
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

/*
 * Each funding case type has to provide a AngularJS module named
 * "crmFunding<funding case type name>". This module has to define a directive
 * named "funding<funding case type name>ApplicationEditor". (In both cases the
 * first character of the funding case type name has to be in uppercase.)
 */
foreach (FundingCaseType::get(FALSE)->addSelect('name')->execute()->column('name') as $fundingCaseTypeName) {
  $module['requires'][] = 'crmFunding' . ucfirst($fundingCaseTypeName);
}

return $module;
