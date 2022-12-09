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

namespace Civi\Funding\Api4\Action\FundingProgramContactRelationType;

use Civi\Api4\FundingProgramContactRelationType;
use Civi\Api4\Generic\BasicGetFieldsAction;
use CRM_Funding_ExtensionUtil as E;

/**
 * @phpstan-type fieldT array<string, array<string, scalar>|scalar[]|scalar|null>&array{name: string}
 */
final class GetFieldsAction extends BasicGetFieldsAction {

  public function __construct() {
    parent::__construct(FundingProgramContactRelationType::_getEntityName(), 'getFields');
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
   * @inheritDoc
   *
   * @return array<fieldT>
   *
   * @noinspection PhpMissingParentCallCommonInspection
   */
  protected function getRecords(): array {
    return [
      [
        'name' => 'name',
        'title' => 'Name',
        'data_type' => 'String',
      ],
      [
        'name' => 'label',
        'title' => 'Label',
        'data_type' => 'String',
      ],
      [
        'name' => 'uiTemplate',
        'title' => 'Angular UI template',
        'data_type' => 'String',
      ],
      [
        'name' => 'help',
        'title' => 'Help',
        'data_type' => 'String',
      ],
    ];
  }

}
