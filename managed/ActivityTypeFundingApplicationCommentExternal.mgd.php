<?php

declare(strict_types = 1);

use Civi\Funding\ActivityTypeIds;
use CRM_Funding_ExtensionUtil as E;

return [
  [
    'name' => 'OptionValue_funding_application_comment_external',
    'entity' => 'OptionValue',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'activity_type',
        'label' => E::ts('External Funding Application Comment'),
        'value' => ActivityTypeIds::FUNDING_APPLICATION_COMMENT_EXTERNAL,
        'name' => 'funding_application_comment_external',
        'grouping' => 'funding',
        'filter' => 0,
        'is_default' => FALSE,
        'weight' => 100,
        'description' => E::ts('Activity type for external funding application process comments'),
        'is_optgroup' => FALSE,
        'is_reserved' => TRUE,
        'is_active' => TRUE,
      ],
    ],
  ],
];
