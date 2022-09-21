<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation in version 3.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\Api4\Action\FundingCaseInfo;

use Civi\Api4\FundingApplicationProcess;
use Civi\Api4\FundingCase;
use Civi\Api4\FundingCaseInfo;
use Civi\Api4\Generic\BasicGetFieldsAction;
use Civi\RemoteTools\Api4\OptionsLoaderInterface;

final class GetFieldsAction extends BasicGetFieldsAction {

  private OptionsLoaderInterface $optionsLoader;

  public function __construct(OptionsLoaderInterface $optionsLoader) {
    parent::__construct(FundingCaseInfo::_getEntityName(), 'getFields');
    $this->optionsLoader = $optionsLoader;
  }

  /**
   * @inerhitDoc
   *
   * @return array<array<string, array<string, scalar>|scalar[]|scalar|null>>
   *
   * @noinspection PhpMissingParentCallCommonInspection
   */
  public function fields(): array {
    return [
      [
        'name' => 'name',
        'data_type' => 'String',
        'description' => ts('Unique field identifier'),
      ],
      [
        'name' => 'nullable',
        'description' => 'Whether a null value is allowed in this field',
        'data_type' => 'Boolean',
        'default_value' => FALSE,
      ],
      [
        'name' => 'options',
        'data_type' => 'Array',
        'default_value' => FALSE,
      ],
      [
        'name' => 'operators',
        'data_type' => 'Array',
        'description' => 'If set, limits the operators that can be used on this field for "get" actions.',
        'default_value' => [],
      ],
      [
        'name' => 'data_type',
        'options' => [
          'Array' => ts('Array'),
          'Boolean' => ts('Boolean'),
          'Date' => ts('Date'),
          'Float' => ts('Float'),
          'Integer' => ts('Integer'),
          'String' => ts('String'),
          'Text' => ts('Text'),
          'Timestamp' => ts('Timestamp'),
        ],
      ],
      [
        'name' => 'readonly',
        'data_type' => 'Boolean',
        'default_value' => TRUE,
      ],
    ];
  }

  /**
   * @inerhitDoc
   *
   * @return array<array<string, array<string, scalar>|scalar[]|scalar|null>>
   *
   * @noinspection PhpMissingParentCallCommonInspection
   */
  public function getRecords(): array {
    return [
      [
        'name' => 'funding_case_id',
        'data_type' => 'Integer',
        'operators' => ['='],
      ],
      [
        'name' => 'funding_case_permissions',
        'data_type' => 'Array',
      ],
      [
        'name' => 'funding_case_status',
        'data_type' => 'String',
        'options' => $this->getOptions(FundingCase::_getEntityName(), 'status'),
      ],
      [
        'name' => 'funding_case_creation_date',
        'data_type' => 'Date',
      ],
      [
        'name' => 'funding_case_modification_date',
        'data_type' => 'Date',
      ],
      [
        'name' => 'funding_case_type_id',
        'data_type' => 'Integer',
      ],
      [
        'name' => 'funding_program_id',
        'data_type' => 'Integer',
      ],
      [
        'name' => 'funding_program_currency',
        'data_type' => 'String',
      ],
      [
        'name' => 'funding_program_title',
        'data_type' => 'String',
      ],
      [
        'name' => 'application_process_id',
        'data_type' => 'Integer',
      ],
      [
        'name' => 'application_process_title',
        'data_type' => 'String',
      ],
      [
        'name' => 'application_process_short_description',
        'data_type' => 'String',
      ],
      [
        'name' => 'application_process_status',
        'data_type' => 'String',
        'options' => $this->getOptions(FundingApplicationProcess::_getEntityName(), 'status'),
      ],
      [
        'name' => 'application_process_is_review_calculative',
        'data_type' => 'Boolean',
        'nullable' => TRUE,
      ],
      [
        'name' => 'application_process_is_review_content',
        'data_type' => 'Boolean',
        'nullable' => TRUE,
      ],
      [
        'name' => 'application_process_amount_requested',
        'data_type' => 'Float',
      ],
      [
        'name' => 'application_process_amount_granted',
        'data_type' => 'Float',
        'nullable' => TRUE,
      ],
      [
        'name' => 'application_process_granted_budget',
        'data_type' => 'Float',
        'nullable' => TRUE,
      ],
      [
        'name' => 'application_process_creation_date',
        'data_type' => 'Date',
      ],
      [
        'name' => 'application_process_modification_date',
        'data_type' => 'Date',
      ],
      [
        'name' => 'application_process_start_date',
        'data_type' => 'Date',
        'nullable' => TRUE,
      ],
      [
        'name' => 'application_process_end_date',
        'data_type' => 'Date',
        'nullable' => TRUE,
      ],
    ];
  }

  /**
   * @return bool|array
   * @phpstan-return bool|array<scalar|null, string>
   *
   * @throws \API_Exception
   */
  private function getOptions(string $entityName, string $field) {
    if (FALSE === $this->loadOptions) {
      return TRUE;
    }

    return $this->optionsLoader->getOptions($entityName, $field);
  }

}
