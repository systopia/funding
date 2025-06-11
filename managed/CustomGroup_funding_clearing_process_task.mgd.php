<?php

use Civi\Funding\ActivityTypeNames;
use CRM_Funding_ExtensionUtil as E;

return [
  [
    'name' => 'CustomGroup_funding_clearing_process_task',
    'entity' => 'CustomGroup',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'funding_clearing_process_task',
        'table_name' => 'civicrm_value_funding_clearing_process_task',
        'title' => E::ts('Funding Clearing Task'),
        'extends' => 'Activity',
        'extends_entity_column_value:name' => [
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
    'name' => 'CustomGroup_funding_clearing_process_task_CustomField_clearing_process_id',
    'entity' => 'CustomField',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'custom_group_id.name' => 'funding_clearing_process_task',
        'name' => 'clearing_process_id',
        'label' => E::ts('Clearing Process'),
        'data_type' => 'EntityReference',
        'html_type' => 'Autocomplete-Select',
        'is_required' => TRUE,
        'is_searchable' => TRUE,
        'text_length' => 255,
        'note_columns' => 60,
        'note_rows' => 4,
        'column_name' => 'clearing_process_id',
        'fk_entity' => 'FundingClearingProcess',
        'fk_entity_on_delete' => 'cascade',
      ],
      'match' => [
        'name',
        'custom_group_id',
      ],
    ],
  ],
];
