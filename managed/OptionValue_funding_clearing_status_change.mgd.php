<?php
declare(strict_types = 1);

use Civi\Funding\ActivityTypeNames;
use CRM_Funding_ExtensionUtil as E;

return [
  [
    'name' => 'OptionValue_funding_clearing_status_change',
    'entity' => 'OptionValue',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'activity_type',
        'label' => E::ts('Funding Clearing Status Change'),
        'name' => ActivityTypeNames::FUNDING_CLEARING_STATUS_CHANGE,
        'grouping' => 'funding',
        'weight' => 76,
        'description' => E::ts('Activity type for funding clearing status changes'),
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
