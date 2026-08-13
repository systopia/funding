<?php

declare(strict_types = 1);

use Civi\Funding\ActivityTypeNames;
use CRM_Funding_ExtensionUtil as E;

return [
  [
    'name' => 'OptionValue_funding_application_move',
    'entity' => 'OptionValue',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'activity_type',
        'label' => E::ts('Funding Application Move'),
        'name' => ActivityTypeNames::FUNDING_APPLICATION_MOVE,
        'grouping' => 'funding',
        'filter' => 0,
        'is_default' => FALSE,
        'description' => E::ts('Activity type when a funding application is moved to another funding case'),
        'is_optgroup' => FALSE,
        'is_reserved' => TRUE,
        'is_active' => TRUE,
      ],
    ],
  ],
];
