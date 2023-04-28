<?php

declare(strict_types = 1);

use Civi\Funding\ApplicationProcess\TaskType;
use CRM_Funding_ExtensionUtil as E;

return [
  [
    'name' => 'CustomGroup_funding_application_task',
    'entity' => 'CustomGroup',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'funding_application_task',
        'table_name' => 'civicrm_value_funding_application_task',
        'title' => E::ts('Funding Application Task'),
        'extends' => 'Activity',
        'extends_entity_column_value:name' => [
          'funding_application_task_external',
          'funding_application_task_internal',
        ],
        'style' => 'Inline',
        'collapse_display' => FALSE,
        'help_pre' => '',
        'help_post' => '',
        'weight' => 1,
        'is_active' => TRUE,
        'is_multiple' => FALSE,
        'collapse_adv_display' => TRUE,
        'is_reserved' => TRUE,
        'is_public' => FALSE,
        'icon' => '',
      ],
    ],
  ],
  [
    'name' => 'OptionGroup_funding_application_task_type',
    'entity' => 'OptionGroup',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'funding_application_task_type',
        'title' => E::ts('Funding application task type'),
        'data_type' => 'String',
        'is_reserved' => TRUE,
        'is_active' => TRUE,
        'is_locked' => TRUE,
        'option_value_fields' => [
          'name',
          'label',
        ],
      ],
    ],
  ],
  [
    'name' => 'OptionValue_funding_application_task_type.review_content',
    'entity' => 'OptionValue',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'funding_application_task_type',
        'label' => E::ts('Review Content'),
        'value' => TaskType::REVIEW_CONTENT,
        'name' => TaskType::REVIEW_CONTENT,
        'filter' => 0,
        'is_default' => FALSE,
        'is_optgroup' => FALSE,
        'is_reserved' => TRUE,
        'is_active' => TRUE,
      ],
    ],
  ],
  [
    'name' => 'OptionValue_funding_application_task_type.review_calculative',
    'entity' => 'OptionValue',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'funding_application_task_type',
        'label' => E::ts('Review Calculative'),
        'value' => TaskType::REVIEW_CALCULATIVE,
        'name' => TaskType::REVIEW_CALCULATIVE,
        'is_default' => FALSE,
        'is_optgroup' => FALSE,
        'is_reserved' => TRUE,
        'is_active' => TRUE,
      ],
    ],
  ],
  [
    'name' => 'OptionValue_funding_application_task_type.rework',
    'entity' => 'OptionValue',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'funding_application_task_type',
        'label' => E::ts('Rework Application'),
        'value' => TaskType::REWORK,
        'name' => TaskType::REWORK,
        'is_default' => FALSE,
        'is_optgroup' => FALSE,
        'is_reserved' => TRUE,
        'is_active' => TRUE,
      ],
    ],
  ],
  [
    'name' => 'CustomField_funding_application_task.type',
    'entity' => 'CustomField',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'custom_group_id.name' => 'funding_application_task',
        'name' => 'type',
        'label' => E::ts('Type'),
        'data_type' => 'String',
        'html_type' => 'Select',
        'is_required' => TRUE,
        'is_searchable' => TRUE,
        'is_search_range' => FALSE,
        'is_active' => TRUE,
        'is_view' => FALSE,
        'text_length' => 64,
        'column_name' => 'type',
        'option_group_id.name' => 'funding_application_task_type',
        'serialize' => 0,
        'in_selector' => FALSE,
      ],
    ],
  ],
];
