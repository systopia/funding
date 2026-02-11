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

namespace Civi\Funding\Api4\Action\FundingCaseType;

use Civi\Api4\FundingCaseType;
use Civi\Api4\Generic\DAOGetFieldsAction;
use CRM_Funding_ExtensionUtil as E;

final class GetFieldsAction extends DAOGetFieldsAction {

  public function __construct() {
    parent::__construct(FundingCaseType::getEntityName(), 'getFields');
  }

  /**
   * @phpstan-return list<array<string, array<string, scalar>|array<scalar>|scalar|null>>
   */
  protected function getRecords(): array {
    /** @var list<array<string, array<string, scalar>|array<scalar>|scalar|null>> $fields */
    $fields = parent::getRecords();
    // Files must already be attached to the entity, so the fields are actually
    // not available on create. However, for Afform we must not exclude them on
    // create action.
    $fields[] = [
      'name' => 'transfer_contract_template_file_id',
      'title' => E::ts('Transfer Contract Template'),
      'description' => 'ID of a file already attached to the entity',
      'type' => 'Extra',
      'data_type' => 'Integer',
      'input_type' => 'File',
      'fk_entity' => 'File',
      // Might be NULL on get until set.
      'nullable' => !str_starts_with($this->action, 'get'),
      'readonly' => 'create' === $this->action,
    ];
    $fields[] = [
      'name' => 'payment_instruction_template_file_id',
      'title' => E::ts('Payment Instruction Template'),
      'description' => 'ID of a file already attached to the entity',
      'type' => 'Extra',
      'data_type' => 'Integer',
      'input_type' => 'File',
      'fk_entity' => 'File',
      // Might be NULL on get until set.
      'nullable' => !str_starts_with($this->action, 'get'),
      'readonly' => 'create' === $this->action,
    ];
    $fields[] = [
      'name' => 'payback_claim_template_file_id',
      'title' => E::ts('Payback Claim Template'),
      'description' => 'ID of a file already attached to the entity',
      'type' => 'Extra',
      'data_type' => 'Integer',
      'input_type' => 'File',
      'fk_entity' => 'File',
      // Might be NULL on get until set.
      'nullable' => !str_starts_with($this->action, 'get'),
      'readonly' => 'create' === $this->action,
    ];
    $fields[] = [
      'name' => 'drawdown_submit_confirmation_template_file_id',
      'title' => E::ts('Drawdown Submit Confirmation Template'),
      'description' => 'ID of a file already attached to the entity',
      'type' => 'Extra',
      'data_type' => 'Integer',
      'input_type' => 'File',
      'fk_entity' => 'File',
      'nullable' => TRUE,
      'readonly' => 'create' === $this->action,
    ];

    return $fields;
  }

}
