<?php
declare(strict_types = 1);

use CRM_Funding_ExtensionUtil as E;

return [
  [
    'name' => 'OptionValue_cg_extend_objects-civicrm_funding_application_process',
    'entity' => 'OptionValue',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'cg_extend_objects',
        'label' => E::ts('Application Process'),
        'value' => 'FundingApplicationProcess',
        'name' => 'civicrm_funding_application_process',
        'is_reserved' => TRUE,
      ],
      'match' => [
        'option_group_id',
        'name',
        'value',
      ],
    ],
  ],
];
