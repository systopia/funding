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

namespace Civi\Funding\Api4\Action\FundingTask;

use Civi\Api4\Activity;
use Civi\Api4\Generic\DAOGetFieldsAction;
use Civi\Funding\ActivityStatusTypes;
use CRM_Funding_ExtensionUtil as E;

final class GetFieldsAction extends DAOGetFieldsAction {

  /**
   * @phpstan-return array<int, string>
   */
  public static function getStatusTypeLabels(): array {
    return [
      ActivityStatusTypes::INCOMPLETE => E::ts('Incomplete'),
      ActivityStatusTypes::COMPLETED => E::ts('Completed'),
      ActivityStatusTypes::CANCELLED => E::ts('Cancelled'),
    ];
  }

  public function __construct() {
    parent::__construct(Activity::getEntityName(), 'getFields');
  }

  /**
   * @phpstan-return array<array<string, array<string, scalar>|array<scalar>|scalar|null>>
   */
  protected function getRecords(): array {
    $fields = parent::getRecords();

    /*
     * This is actually the value of the filter field of the option value
     * referenced by status_id. See GetAction for the implementation.
     */
    $fields[] = [
      'type' => 'Extra',
      'nullable' => TRUE,
      'readonly' => TRUE,
      'entity' => 'FundingTask',
      'name' => 'status_type_id',
      'title' => E::ts('Status Type'),
      'data_type' => 'Integer',
      'options' => $this->getStatusTypeIdOptions(),
      'suffixes' => [
        'name',
        'label',
      ],
    ];

    // Allow to change the ignoreTaskPermissions parameter in SearchKit via filter.
    $fields[] = [
      'type' => 'Filter',
      'nullable' => TRUE,
      'readonly' => TRUE,
      'entity' => 'FundingTask',
      'name' => 'ignore_task_permissions',
      'title' => E::ts('Ignore Task Permissions'),
      'data_type' => 'Boolean',
      'operators' => ['='],
    ];

    return $fields;
  }

  /**
   * @phpstan-return array<int, string>|list<array{id: int, name: string, label: string}>|true
   */
  private function getStatusTypeIdOptions(): array|bool {
    switch ($this->getLoadOptions()) {
      case FALSE:
        return TRUE;

      case TRUE:
        return self::getStatusTypeLabels();

      default:
        $options = [];
        foreach (self::getStatusTypeLabels() as $statusTypeId => $statusTypeLabel) {
          $options[] = [
            'id' => $statusTypeId,
            'name' => (string) $statusTypeId,
            'label' => $statusTypeLabel,
          ];
        }

        return $options;
    }
  }

}
