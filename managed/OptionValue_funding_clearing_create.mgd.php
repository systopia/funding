<?php
declare(strict_types = 1);

use Civi\Funding\ActivityTypeNames;
use CRM_Funding_ExtensionUtil as E;

return [
  [
    'name' => 'OptionValue_funding_clearing_create',
    'entity' => 'OptionValue',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'activity_type',
        'label' => E::ts('Funding Clearing Started'),
        'name' => ActivityTypeNames::FUNDING_CLEARING_CREATE,
        'grouping' => 'funding',
        'weight' => 74,
        'description' => E::ts('Activity type for funding clearing creation'),
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
