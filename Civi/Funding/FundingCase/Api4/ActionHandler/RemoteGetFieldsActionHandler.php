<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\Funding\FundingCase\Api4\ActionHandler;

use Civi\Api4\FundingCase;
use Civi\Funding\Api4\ActionHandler\AbstractRemoteFundingGetFieldsActionHandler;
use Civi\RemoteTools\Api4\Action\AbstractRemoteGetFieldsAction;
use CRM_Funding_ExtensionUtil as E;

final class RemoteGetFieldsActionHandler extends AbstractRemoteFundingGetFieldsActionHandler {

  public const ENTITY_NAME = 'RemoteFundingCase';

  public function getFields(AbstractRemoteGetFieldsAction $action): array {
    $fields = parent::getFields($action);
    $fields[] = [
      'nullable' => FALSE,
      'name' => 'funding_case_type_id.is_combined_application',
      'title' => E::ts('Is Combined Application'),
      'data_type' => 'Boolean',
      'serialize' => NULL,
      'options' => FALSE,
      'label' => E::ts('Is Combined Application'),
    ];

    $fields[] = [
      'nullable' => TRUE,
      'name' => 'funding_case_type_id.application_process_label',
      'title' => E::ts('Application Process Label'),
      'data_type' => 'String',
      'serialize' => NULL,
      'options' => FALSE,
      'label' => E::ts('Application Process Label'),
    ];

    return $fields;
  }

  protected function getEntityName(): string {
    return FundingCase::getEntityName();
  }

}
