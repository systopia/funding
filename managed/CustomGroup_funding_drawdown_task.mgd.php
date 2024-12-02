<?php

use Civi\Funding\ActivityTypeNames;
use CRM_Funding_ExtensionUtil as E;

return [
  [
    'name' => 'CustomGroup_funding_drawdown_task',
    'entity' => 'CustomGroup',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'funding_drawdown_task',
        'table_name' => 'civicrm_value_funding_drawdown_task',
        'title' => E::ts('Funding Payout Task'),
        'extends' => 'Activity',
        'extends_entity_column_value:name' => [
          ActivityTypeNames::DRAWDOWN_TASK,
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
    'name' => 'CustomGroup_funding_drawdown_task_CustomField_drawdown_id',
    'entity' => 'CustomField',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'custom_group_id.name' => 'funding_drawdown_task',
        'name' => 'drawdown_id',
        'label' => E::ts('Drawdown'),
        'data_type' => 'EntityReference',
        'html_type' => 'Autocomplete-Select',
        'is_required' => TRUE,
        'column_name' => 'drawdown_id',
        'fk_entity' => 'FundingDrawdown',
        'fk_entity_on_delete' => 'cascade',
      ],
      'match' => [
        'name',
        'custom_group_id',
      ],
    ],
  ],
];
