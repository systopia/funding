<?php

declare(strict_types = 1);

use Civi\Funding\ActivityTypeNames;
use CRM_Funding_ExtensionUtil as E;

return [
  [
    'name' => 'OptionValue_funding_application_process_task',
    'entity' => 'OptionValue',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'activity_type',
        'label' => E::ts('Funding Application Task'),
        'name' => ActivityTypeNames::APPLICATION_PROCESS_TASK,
        'filter' => 0,
        'is_default' => FALSE,
        'weight' => 100,
        'description' => E::ts('Activity type for funding application tasks.'),
        'is_optgroup' => FALSE,
        'is_reserved' => TRUE,
        'is_active' => TRUE,
      ],
    ],
  ],
];
