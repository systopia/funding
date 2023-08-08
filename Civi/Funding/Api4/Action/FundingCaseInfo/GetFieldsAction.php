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
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\OptionsLoaderInterface;
use Civi\RemoteTools\Api4\RemoteApiConstants;
use CRM_Funding_ExtensionUtil as E;

final class GetFieldsAction extends BasicGetFieldsAction {

  private Api4Interface $api4;

  private OptionsLoaderInterface $optionsLoader;

  public function __construct(Api4Interface $api4, OptionsLoaderInterface $optionsLoader) {
    parent::__construct(FundingCaseInfo::_getEntityName(), 'getFields');
    $this->api4 = $api4;
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
        'description' => E::ts('Unique field identifier'),
      ],
      [
        'name' => 'title',
        'data_type' => 'String',
        'description' => E::ts('Technical name of field, shown in API and exports'),
      ],
      [
        'name' => 'type',
        'data_type' => 'String',
        'default_value' => 'Extra',
        'options' => [
          'Field' => E::ts('Primary Field'),
          'Custom' => E::ts('Custom Field'),
          'Filter' => E::ts('Search Filter'),
          'Extra' => E::ts('Extra API Field'),
        ],
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
          'Array' => E::ts('Array'),
          'Boolean' => E::ts('Boolean'),
          'Date' => E::ts('Date'),
          'Float' => E::ts('Float'),
          'Integer' => E::ts('Integer'),
          'String' => E::ts('String'),
          'Text' => E::ts('Text'),
          'Timestamp' => E::ts('Timestamp'),
        ],
      ],
      [
        'name' => 'serialize',
        'data_type' => 'Integer',
        'default_value' => 0,
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
   * @throws \CRM_Core_Exception
   *
   * @noinspection PhpMissingParentCallCommonInspection
   */
  public function getRecords(): array {
    return array_merge([
      [
        'name' => 'funding_case_id',
        'title' => 'funding_case_id',
        'data_type' => 'Integer',
        'operators' => ['='],
      ],
      [
        'name' => 'funding_case_permissions',
        'title' => 'funding_case_permissions',
        'data_type' => 'String',
        'serialize' => 1,
        'options' => $this->getOptions('FundingCase', 'permissions'),
      ],
      [
        'name' => 'funding_case_status',
        'title' => 'funding_case_status',
        'data_type' => 'String',
        'options' => $this->getOptions(FundingCase::_getEntityName(), 'status'),
      ],
      [
        'name' => 'funding_case_creation_date',
        'title' => 'funding_case_creation_date',
        'data_type' => 'Date',
      ],
      [
        'name' => 'funding_case_modification_date',
        'title' => 'funding_case_modification_date',
        'data_type' => 'Date',
      ],
      [
        'name' => 'funding_case_title',
        'title' => 'funding_case_title',
        'data_type' => 'String',
        'nullable' => TRUE,
      ],
      [
        'name' => 'funding_case_amount_approved',
        'title' => 'funding_case_amount_approved',
        'data_type' => 'Float',
        'nullable' => TRUE,
      ],
      [
        'name' => 'funding_case_type_id',
        'title' => 'funding_case_type_id',
        'data_type' => 'Integer',
      ],
      [
        'name' => 'funding_case_type_is_summary_application',
        'title' => 'funding_case_type_is_summary_application',
        'data_type' => 'Boolean',
        'operators' => ['=', '!='],
      ],
      [
        'name' => 'funding_case_transfer_contract_uri',
        'title' => 'funding_case_transfer_contract_uri',
        'data_type' => 'String',
        'nullable' => TRUE,
      ],
      [
        'name' => 'funding_program_id',
        'title' => 'funding_program_id',
        'data_type' => 'Integer',
      ],
      [
        'name' => 'funding_program_currency',
        'title' => 'funding_program_currency',
        'data_type' => 'String',
      ],
      [
        'name' => 'funding_program_title',
        'title' => 'funding_program_title',
        'data_type' => 'String',
      ],
      [
        'name' => 'application_process_id',
        'title' => 'application_process_id',
        'data_type' => 'Integer',
      ],
      [
        'name' => 'application_process_identifier',
        'title' => 'application_process_identifier',
        'data_type' => 'String',
      ],
      [
        'name' => 'application_process_title',
        'title' => 'application_process_title',
        'data_type' => 'String',
      ],
      [
        'name' => 'application_process_short_description',
        'title' => 'application_process_short_description',
        'data_type' => 'String',
      ],
      [
        'name' => 'application_process_status',
        'title' => 'application_process_status',
        'data_type' => 'String',
        'options' => $this->getOptions(FundingApplicationProcess::_getEntityName(), 'status'),
      ],
      [
        'name' => 'application_process_is_review_calculative',
        'title' => 'application_process_is_review_calculative',
        'data_type' => 'Boolean',
        'nullable' => TRUE,
      ],
      [
        'name' => 'application_process_is_review_content',
        'title' => 'application_process_is_review_content',
        'data_type' => 'Boolean',
        'nullable' => TRUE,
      ],
      [
        'name' => 'application_process_amount_requested',
        'title' => 'application_process_amount_requested',
        'data_type' => 'Float',
      ],
      [
        'name' => 'application_process_creation_date',
        'title' => 'application_process_creation_date',
        'data_type' => 'Date',
      ],
      [
        'name' => 'application_process_modification_date',
        'title' => 'application_process_modification_date',
        'data_type' => 'Date',
      ],
      [
        'name' => 'application_process_start_date',
        'title' => 'application_process_start_date',
        'data_type' => 'Date',
        'nullable' => TRUE,
      ],
      [
        'name' => 'application_process_end_date',
        'title' => 'application_process_end_date',
        'data_type' => 'Date',
        'nullable' => TRUE,
      ],
      [
        'name' => 'application_process_is_eligible',
        'title' => 'application_process_is_eligible',
        'data_type' => 'Boolean',
        'nullable' => TRUE,
      ],
    ], iterator_to_array($this->getPermissionFields()));
  }

  /**
   * @return bool|array
   * @phpstan-return bool|array<scalar|null, string>
   *
   * @throws \CRM_Core_Exception
   */
  private function getOptions(string $entityName, string $field) {
    if (FALSE === $this->loadOptions) {
      return TRUE;
    }

    return $this->optionsLoader->getOptions($entityName, $field);
  }

  /**
   * @phpstan-return iterable<array<string, mixed>&array{name: string}>
   *
   * @throws \CRM_Core_Exception
   */
  private function getPermissionFields(): iterable {
    $action = FundingCase::getFields($this->getCheckPermissions());
    $result = $this->api4->executeAction($action);

    /** @var array<string, mixed>&array{name: string} $field */
    foreach ($result as $field) {
      if (str_starts_with($field['name'], RemoteApiConstants::PERMISSION_FIELD_PREFIX)) {
        $field['name'] = 'funding_case_' . $field['name'];
        yield $field;
      }
    }
  }

}
