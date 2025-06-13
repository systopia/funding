<?php

use Civi\Funding\ActivityTypeNames;
use CRM_Funding_ExtensionUtil as E;

return [
  [
    'name' => 'CustomGroup_funding_application_process_task',
    'entity' => 'CustomGroup',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'funding_application_process_task',
        'table_name' => 'civicrm_value_funding_application_process_task',
        'title' => E::ts('Funding Application Process Task'),
        'extends' => 'Activity',
        'extends_entity_column_value:name' => [
          ActivityTypeNames::APPLICATION_PROCESS_TASK,
          ActivityTypeNames::CLEARING_PROCESS_TASK,
        ],
        'style' => 'Inline',
        'collapse_display' => TRUE,
        'help_pre' => '',
        'help_post' => '',
        'collapse_adv_display' => TRUE,
        'icon' => '',
      ],
      'match' => [
        'name',
      ],
    ],
  ],
  [
    'name' => 'CustomGroup_funding_application_process_task_CustomField_application_process_id',
    'entity' => 'CustomField',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'custom_group_id.name' => 'funding_application_process_task',
        'name' => 'application_process_id',
        'label' => E::ts('Application Process'),
        'data_type' => 'EntityReference',
        'html_type' => 'Autocomplete-Select',
        'is_required' => TRUE,
        'is_searchable' => TRUE,
        'text_length' => 255,
        'note_columns' => 60,
        'note_rows' => 4,
        'column_name' => 'application_process_id',
        'fk_entity' => 'FundingApplicationProcess',
        'fk_entity_on_delete' => 'cascade',
      ],
      'match' => [
        'name',
        'custom_group_id',
      ],
    ],
  ],
];
