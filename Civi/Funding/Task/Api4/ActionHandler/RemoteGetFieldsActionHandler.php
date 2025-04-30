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

namespace Civi\Funding\Task\Api4\ActionHandler;

use Civi\Api4\FundingTask;
use Civi\Funding\Api4\ActionHandler\AbstractRemoteFundingGetFieldsActionHandler;
use Civi\RemoteTools\Api4\Action\AbstractRemoteGetFieldsAction;
use CRM_Funding_ExtensionUtil as E;

final class RemoteGetFieldsActionHandler extends AbstractRemoteFundingGetFieldsActionHandler {

  public const ENTITY_NAME = 'RemoteFundingTask';

  protected function getEntityName(): string {
    return FundingTask::getEntityName();
  }

  public function getFields(AbstractRemoteGetFieldsAction $action): array {
    $fields = parent::getFields($action);
    $fields[] = [
      'type' => 'Extra',
      'name' => 'funding_case_task.funding_case_id.funding_case_type_id.is_combined_application',
      'nullable' => FALSE,
      'title' => E::ts('Is Combined Application'),
      'data_type' => 'Boolean',
      'input_type' => 'CheckBox',
    ];

    $fields[] = [
      'type' => 'Extra',
      'name' => 'funding_case_task.funding_case_id.funding_case_type_id.application_process_label',
      'nullable' => TRUE,
      'title' => E::ts('Application Process Label'),
      'description' => E::ts('Used for combined applications'),
      'data_type' => 'String',
      'input_type' => 'Text',
      'input_attrs' => [
        'maxlength' => 255,
      ],
    ];

    return $fields;
  }

}
