<?php
use CRM_Funding_ExtensionUtil as E;

// Note: We use in-place edit as well as a separate edit form because with
// in-place edit only a tiny single line input field is shown which isn't
// useful for longer texts.
return [
  [
    'name' => 'SavedSearch_funding_form_string_translation',
    'entity' => 'SavedSearch',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'funding_form_string_translation',
        'label' => E::ts('Form String Translation'),
        'api_entity' => 'FundingFormStringTranslation',
        'api_params' => [
          'version' => 4,
          'select' => [
            'id',
            'funding_case_type_id:label',
            'funding_program_id:label',
            'msg_text',
            'new_text',
            'modification_date',
          ],
          'orderBy' => [],
          'where' => [],
          'groupBy' => [],
          'join' => [],
          'having' => [],
        ],
      ],
      'match' => [
        'name',
      ],
    ],
  ],
  [
    'name' => 'SavedSearch_funding_form_string_translation_SearchDisplay_translation',
    'entity' => 'SearchDisplay',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'translation',
        'label' => E::ts('Translation'),
        'saved_search_id.name' => 'funding_form_string_translation',
        'type' => 'table',
        'settings' => [
          'description' => NULL,
          'sort' => [
            [
              'msg_text',
              'ASC',
            ],
          ],
          'limit' => 20,
          'pager' => [],
          'placeholder' => 5,
          'columns' => [
            [
              'type' => 'field',
              'key' => 'msg_text',
              'dataType' => 'Text',
              'label' => E::ts('Original'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'new_text',
              'dataType' => 'Text',
              'label' => E::ts('Actual'),
              'sortable' => TRUE,
              'editable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'modification_date',
              'dataType' => 'Timestamp',
              'label' => E::ts('Modification Date'),
              'sortable' => TRUE,
            ],
            [
              'size' => 'btn-xs',
              'links' => [
                [
                  'path' => 'civicrm/funding/funding/form-string-translation/edit#?FundingFormStringTranslation1=[id]',
                  'icon' => 'fa-pen-to-square',
                  'text' => E::ts('Edit string'),
                  'style' => 'default',
                  'conditions' => [],
                  'task' => '',
                  'entity' => '',
                  'action' => '',
                  'join' => '',
                  'target' => 'crm-popup',
                ],
              ],
              'type' => 'buttons',
              'alignment' => 'text-right',
            ],
          ],
          'actions' => [
            'download',
          ],
          'classes' => [
            'table',
            'table-striped',
          ],
          'actions_display_mode' => 'menu',
        ],
      ],
      'match' => [
        'saved_search_id',
        'name',
      ],
    ],
  ],
];
