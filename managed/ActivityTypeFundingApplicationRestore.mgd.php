<?php

declare(strict_types = 1);

use Civi\Funding\ActivityTypeIds;
use CRM_Funding_ExtensionUtil as E;

return [
  [
    'name' => 'OptionValue_funding_application_restore',
    'entity' => 'OptionValue',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'activity_type',
        'label' => E::ts('Funding Application Restore'),
        'value' => ActivityTypeIds::FUNDING_APPLICATION_RESTORE,
        'name' => 'funding_application_restore',
        'grouping' => 'funding',
        'filter' => 0,
        'is_default' => FALSE,
        'weight' => 100,
        'description' => E::ts('Activity type for funding application restore'),
        'is_optgroup' => FALSE,
        'is_reserved' => TRUE,
        'is_active' => TRUE,
      ],
    ],
  ],
];