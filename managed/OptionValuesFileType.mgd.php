<?php
declare(strict_types = 1);

use Civi\Funding\FileTypeNames;
use CRM_Funding_ExtensionUtil as E;

return [
  [
    'name' => 'OptionValue_file_type.transfer_contract',
    'entity' => 'OptionValue',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'file_type',
        'label' => E::ts('Transfer Contract'),
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
    'name' => 'OptionValue_file_type.transfer_contract_template',
    'entity' => 'OptionValue',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'file_type',
        'label' => E::ts('Transfer Contract Template'),
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
    'name' => 'OptionValue_file_type.payment_instruction',
    'entity' => 'OptionValue',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'file_type',
        'label' => E::ts('Payment Instruction'),
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
    'name' => 'OptionValue_file_type.payment_instruction_template',
    'entity' => 'OptionValue',
    'cleanup' => 'unused',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'file_type',
        'label' => E::ts('Payment Instruction Template'),
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
