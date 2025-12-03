<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

namespace Civi\Funding\ApplicationProcess\Api4\ActionHandler;

use Civi\Api4\FundingApplicationProcess;
use Civi\Api4\FundingCase;
use Civi\Api4\FundingClearingProcess;
use Civi\Funding\Api4\ActionHandler\AbstractRemoteFundingGetFieldsActionHandler;
use Civi\Funding\Api4\OptionsLoaderInterface;
use Civi\RemoteTools\Api4\Action\AbstractRemoteGetFieldsAction;
use Civi\RemoteTools\Api4\Api4Interface;
use CRM_Funding_ExtensionUtil as E;

final class RemoteGetFieldsActionHandler extends AbstractRemoteFundingGetFieldsActionHandler {

  public const ENTITY_NAME = 'RemoteFundingApplicationProcess';

  private OptionsLoaderInterface $optionsLoader;

  public function __construct(Api4Interface $api4, OptionsLoaderInterface $optionsLoader) {
    parent::__construct($api4);
    $this->optionsLoader = $optionsLoader;
  }

  public function getFields(AbstractRemoteGetFieldsAction $action): array {
    $fields = parent::getFields($action);
    $fields[] = [
      'type' => 'Extra',
      'name' => 'funding_case_id.identifier',
      'nullable' => TRUE,
      'title' => E::ts('Funding Case Identifier'),
      'data_type' => 'String',
    ];

    $fields[] = [
      'type' => 'Extra',
      'name' => 'funding_case_id.permissions',
      'title' => E::ts('Permissions'),
      'data_type' => 'String',
      'serialize' => \CRM_Core_DAO::SERIALIZE_JSON,
      'options' => $this->getOptions($action, 'FundingCase', 'permissions'),
      'nullable' => FALSE,
    ];

    $fields[] = [
      'type' => 'Extra',
      'name' => 'funding_case_id.status',
      'title' => E::ts('Funding Case Status'),
      'data_type' => 'String',
      'options' => $this->getOptions($action, FundingCase::getEntityName(), 'status'),
      'input_type' => 'Select',
      'nullable' => FALSE,
    ];

    $fields[] = [
      'type' => 'Extra',
      'name' => 'funding_case_id.creation_date',
      'title' => E::ts('Funding Case Creation Date'),
      'data_type' => 'Date',
      'nullable' => FALSE,
    ];

    $fields[] = [
      'type' => 'Extra',
      'name' => 'funding_case_id.modification_date',
      'title' => E::ts('Funding Case Modification Date'),
      'data_type' => 'Date',
      'nullable' => FALSE,
    ];

    $fields[] = [
      'type' => 'Extra',
      'name' => 'funding_case_id.amount_approved',
      'title' => E::ts('Amount Approved'),
      'data_type' => 'Float',
      'nullable' => TRUE,
    ];

    $fields[] = [
      'type' => 'Extra',
      'name' => 'funding_case_id.creation_contact_id',
      'title' => 'funding_case_creation_contact_id',
      'data_type' => 'Integer',
      'nullable' => FALSE,
    ];

    $fields[] = [
      'type' => 'Extra',
      'name' => 'funding_case_id.creation_contact_id.display_name',
      'title' => E::ts('Creation Contact'),
      'data_type' => 'String',
      'nullable' => FALSE,
    ];

    $fields[] = [
      'type' => 'Extra',
      'name' => 'funding_case_id.recipient_contact_id',
      'title' => 'funding_case_recipient_contact_id',
      'data_type' => 'Integer',
      'nullable' => FALSE,
    ];

    $fields[] = [
      'type' => 'Extra',
      'name' => 'funding_case_id.recipient_contact_id.display_name',
      'title' => E::ts('Recipient'),
      'data_type' => 'String',
      'nullable' => FALSE,
    ];

    $fields[] = [
      'type' => 'Extra',
      'name' => 'funding_case_id.funding_case_type_id',
      'title' => 'funding_case_type_id',
      'data_type' => 'Integer',
      'nullable' => FALSE,
    ];

    $fields[] = [
      'type' => 'Extra',
      'name' => 'funding_case_id.funding_case_type_id.is_combined_application',
      'title' => E::ts('Is Combined Application'),
      'data_type' => 'Boolean',
      'nullable' => FALSE,
    ];

    $fields[] = [
      'type' => 'Extra',
      'name' => 'funding_case.transfer_contract_uri',
      'title' => 'funding_case_transfer_contract_uri',
      'data_type' => 'String',
      'nullable' => TRUE,
      'operators' => [],
    ];

    $fields[] = [
      'type' => 'Extra',
      'name' => 'funding_case_id.funding_program_id',
      'title' => 'funding_program_id',
      'data_type' => 'Integer',
      'nullable' => FALSE,
    ];

    $fields[] = [
      'type' => 'Extra',
      'name' => 'funding_case_id.funding_program_id.title',
      'title' => E::ts('Funding Program'),
      'data_type' => 'String',
      'nullable' => FALSE,
    ];

    $fields[] = [
      'type' => 'Extra',
      'name' => 'funding_clearing_process.status',
      'nullable' => TRUE,
      'title' => E::ts('Clearing Status'),
      'data_type' => 'String',
      'options' => $this->getOptions($action, FundingClearingProcess::getEntityName(), 'status'),
      'input_type' => 'Select',
    ];

    return $fields;
  }

  protected function getEntityName(): string {
    return FundingApplicationProcess::getEntityName();
  }

  /**
   * @phpstan-return bool|array<string|int, string>
   *
   * @throws \CRM_Core_Exception
   */
  private function getOptions(AbstractRemoteGetFieldsAction $action, string $entityName, string $field) {
    if (FALSE === $action->getLoadOptions()) {
      return TRUE;
    }

    return $this->optionsLoader->getOptions($entityName, $field);
  }

}
