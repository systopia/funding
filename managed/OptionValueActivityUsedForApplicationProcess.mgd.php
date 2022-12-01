<?php

declare(strict_types = 1);

return [
  [
    'name' => 'OptionValue_civicrm_funding_application_process',
    'entity' => 'OptionValue',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'activity_used_for',
        'label' => 'civicrm_funding_application_process',
        'value' => 'civicrm_funding_application_process',
        'name' => 'civicrm_funding_application_process',
        'grouping' => 'funding',
        'filter' => 0,
        'is_default' => FALSE,
        'weight' => 1,
        'description' => NULL,
        'is_optgroup' => FALSE,
        'is_reserved' => TRUE,
        'is_active' => TRUE,
        'component_id' => NULL,
        'domain_id' => NULL,
        'visibility_id' => NULL,
        'icon' => NULL,
        'color' => NULL,
      ],
    ],
  ],
];
