<?php

declare(strict_types = 1);

use Civi\Funding\ActivityTypeNames;
use CRM_Funding_ExtensionUtil as E;

return [
  [
    'name' => 'OptionValue_funding_application_comment_applicant',
    'entity' => 'OptionValue',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'activity_type',
        'label' => E::ts('Funding Application Comment by Applicant'),
        'name' => ActivityTypeNames::FUNDING_APPLICATION_COMMENT_APPLICANT,
        'grouping' => 'funding',
        'filter' => 0,
        'is_default' => FALSE,
        'description' => E::ts('Activity type for funding application comments by applicants'),
        'is_optgroup' => FALSE,
        'is_reserved' => TRUE,
        'is_active' => TRUE,
      ],
    ],
  ],
];
