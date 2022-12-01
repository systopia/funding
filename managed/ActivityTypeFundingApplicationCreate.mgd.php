<?php

declare(strict_types = 1);

use Civi\Funding\ActivityTypeIds;

return [
  [
    'name' => 'OptionValue_funding_application_create',
    'entity' => 'OptionValue',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'activity_type',
        'label' => 'Funding Application Created',
        'value' => ActivityTypeIds::FUNDING_APPLICATION_CREATE,
        'name' => 'funding_application_create',
        'grouping' => 'funding',
        'filter' => 0,
        'is_default' => FALSE,
        'weight' => 100,
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
