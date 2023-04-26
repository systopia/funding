<?php
declare(strict_types = 1);

use Civi\Funding\FileTypeIds;
use Civi\Funding\FileTypeNames;
use CRM_Funding_ExtensionUtil as E;

return [
  [
    'name' => 'OptionGroup_funding_file_type',
    'entity' => 'OptionGroup',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        // Might be replaced by file_type group in core. https://github.com/civicrm/civicrm-core/pull/25904
        'name' => 'funding_file_type',
        'title' => 'Funding File Type',
        'description' => 'File types in the funding extension',
        'data_type' => 'String',
        'is_reserved' => TRUE,
        'is_active' => TRUE,
        'is_locked' => FALSE,
        'option_value_fields' => [
          'name',
          'label',
          'description',
        ],
      ],
    ],
  ],
  [
    'name' => 'OptionValue_funding_file_type.transfer_contract',
    'entity' => 'OptionValue',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'funding_file_type',
        'label' => E::ts('Transfer contract'),
        'value' => FileTypeIds::TRANSFER_CONTRACT,
        'name' => FileTypeNames::TRANSFER_CONTRACT,
        'grouping' => 'funding',
        'filter' => 0,
        'is_default' => FALSE,
        'weight' => 1,
        'is_optgroup' => FALSE,
        'is_reserved' => TRUE,
        'is_active' => TRUE,
      ],
    ],
  ],
  [
    'name' => 'OptionValue_funding_file_type.transfer_contract_template',
    'entity' => 'OptionValue',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'funding_file_type',
        'label' => E::ts('Transfer contract template'),
        'value' => FileTypeIds::TRANSFER_CONTRACT_TEMPLATE,
        'name' => FileTypeNames::TRANSFER_CONTRACT_TEMPLATE,
        'grouping' => 'funding',
        'filter' => 0,
        'is_default' => FALSE,
        'weight' => 1,
        'is_optgroup' => FALSE,
        'is_reserved' => TRUE,
        'is_active' => TRUE,
      ],
    ],
  ],
  [
    'name' => 'OptionValue_funding_file_type.payment_instruction',
    'entity' => 'OptionValue',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'funding_file_type',
        'label' => E::ts('Payment Instruction'),
        'value' => FileTypeIds::PAYMENT_INSTRUCTION,
        'name' => FileTypeNames::PAYMENT_INSTRUCTION,
        'grouping' => 'funding',
        'filter' => 0,
        'is_default' => FALSE,
        'weight' => 1,
        'is_optgroup' => FALSE,
        'is_reserved' => TRUE,
        'is_active' => TRUE,
      ],
    ],
  ],
  [
    'name' => 'OptionValue_funding_file_type.payment_instruction_template',
    'entity' => 'OptionValue',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'funding_file_type',
        'label' => E::ts('Payment Instruction Template'),
        'value' => FileTypeIds::PAYMENT_INSTRUCTION_TEMPLATE,
        'name' => FileTypeNames::PAYMENT_INSTRUCTION_TEMPLATE,
        'grouping' => 'funding',
        'filter' => 0,
        'is_default' => FALSE,
        'weight' => 1,
        'is_optgroup' => FALSE,
        'is_reserved' => TRUE,
        'is_active' => TRUE,
      ],
    ],
  ],
];
