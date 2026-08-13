<?php

use Civi\Funding\ActivityTypeNames;
use CRM_Funding_ExtensionUtil as E;

return [
  [
    'name' => 'CustomGroup_funding_application_move',
    'entity' => 'CustomGroup',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'funding_application_move',
        'table_name' => 'civicrm_value_funding_application_move',
        'title' => E::ts('Funding Application Process Move'),
        'extends' => 'Activity',
        'extends_entity_column_value:name' => [
          ActivityTypeNames::FUNDING_APPLICATION_MOVE,
        ],
        'style' => 'Inline',
        'collapse_display' => TRUE,
        'help_pre' => '',
        'help_post' => '',
        'collapse_adv_display' => TRUE,
        'icon' => '',
        'is_active' => TRUE,
      ],
      'match' => [
        'name',
      ],
    ],
  ],
  [
    'name' => 'CustomGroup_funding_application_move_CustomField_previous_application_process_identifier',
    'entity' => 'CustomField',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'custom_group_id.name' => 'funding_application_move',
        'name' => 'previous_application_process_identifier',
        'label' => E::ts('Previous Application Identifier'),
        'data_type' => 'String',
        'html_type' => 'Text',
        'is_required' => TRUE,
        'is_searchable' => FALSE,
        'text_length' => 255,
        'note_columns' => 60,
        'note_rows' => 4,
        'column_name' => 'previous_application_process_identifier',
        'is_active' => TRUE,
      ],
      'match' => [
        'name',
        'custom_group_id',
      ],
    ],
  ],
  [
    'name' => 'CustomGroup_funding_application_move_CustomField_from_funding_case_identifier',
    'entity' => 'CustomField',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'custom_group_id.name' => 'funding_application_move',
        'name' => 'from_funding_case_identifier',
        'label' => E::ts('From Funding Case With Identifier'),
        'data_type' => 'String',
        'html_type' => 'Text',
        'is_required' => TRUE,
        'is_searchable' => FALSE,
        'text_length' => 255,
        'note_columns' => 60,
        'note_rows' => 4,
        'column_name' => 'from_funding_case_identifier',
        'is_active' => TRUE,
      ],
      'match' => [
        'name',
        'custom_group_id',
      ],
    ],
  ],
  [
    'name' => 'CustomGroup_funding_application_move_CustomField_to_funding_case_identifier',
    'entity' => 'CustomField',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'custom_group_id.name' => 'funding_application_move',
        'name' => 'to_funding_case_identifier',
        'label' => E::ts('To Funding Case With Identifier'),
        'data_type' => 'String',
        'html_type' => 'Text',
        'is_required' => TRUE,
        'is_searchable' => FALSE,
        'text_length' => 255,
        'note_columns' => 60,
        'note_rows' => 4,
        'column_name' => 'to_funding_case_identifier',
        'is_active' => TRUE,
      ],
      'match' => [
        'name',
        'custom_group_id',
      ],
    ],
  ],
];
