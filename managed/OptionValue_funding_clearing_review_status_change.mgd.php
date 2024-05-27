<?php
declare(strict_types = 1);

use Civi\Funding\ActivityTypeNames;
use CRM_Funding_ExtensionUtil as E;

return [
  [
    'name' => 'OptionValue_funding_clearing_review_status_change',
    'entity' => 'OptionValue',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'activity_type',
        'label' => E::ts('Funding Clearing Review Status Change'),
        'name' => ActivityTypeNames::FUNDING_CLEARING_REVIEW_STATUS_CHANGE,
        'grouping' => 'funding',
        'weight' => 75,
        'description' => E::ts('Activity type for funding clearing review status changes'),
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
