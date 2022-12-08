<?php

declare(strict_types = 1);

use Civi\Funding\ActivityTypeIds;
use CRM_Funding_ExtensionUtil as E;

return [
  [
    'name' => 'OptionValue_funding_application_status_change',
    'entity' => 'OptionValue',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'activity_type',
        'label' => E::ts('Funding Application Status Change'),
        'value' => ActivityTypeIds::FUNDING_APPLICATION_STATUS_CHANGE,
        'name' => 'funding_application_status_change',
        'grouping' => 'funding',
        'filter' => 0,
        'is_default' => FALSE,
        'weight' => 100,
        'description' => E::ts('Activity type for funding application process status changes'),
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
