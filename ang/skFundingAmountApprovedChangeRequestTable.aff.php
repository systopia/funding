<?php

declare(strict_types = 1);

use CRM_Funding_ExtensionUtil as E;

return [
  'type' => 'search',
  'title' => E::ts('Funding Amount Approved Change Request Table'),
  'permission' => [
    'access Funding',
  ],
  'requires' => [],
  'description' => '',
  'is_dashlet' => FALSE,
  'is_public' => FALSE,
  'is_token' => FALSE,
  'entity_type' => NULL,
  'join_entity' => NULL,
  'contact_summary' => NULL,
  'summary_contact_type' => NULL,
  'redirect' => NULL,
  'create_submission' => NULL,
];
