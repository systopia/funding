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

namespace Civi\Funding\Api4\Action\FundingDrawdown;

use Civi\Api4\FundingDrawdown;
use Civi\Api4\Generic\DAOGetFieldsAction;
use Civi\Api4\Query\Api4SelectQuery;
use Civi\Funding\Api4\Query\AliasSqlRenderer;
use Civi\Funding\Api4\Query\Util\SqlRendererUtil;
use CRM_Funding_ExtensionUtil as E;

/**
 * @codeCoverageIgnore
 */
final class GetFieldsAction extends DAOGetFieldsAction {

  public function __construct() {
    parent::__construct(FundingDrawdown::getEntityName(), 'getFields');
  }

  /**
   * @phpstan-return array<array<string, array<string, scalar>|array<scalar>|scalar|null>&array{name: string}>
   */
  protected function getRecords(): array {
    return array_merge(parent::getRecords(), [
      [
        'name' => 'currency',
        'title' => E::ts('Currency'),
        'type' => 'Extra',
        'data_type' => 'String',
        'readonly' => TRUE,
        'sql_renderer' => new AliasSqlRenderer('payout_process_id.funding_case_id.funding_program_id.currency'),
      ],
      [
        'name' => 'amount_accepted',
        'title' => E::ts('Amount Accepted'),
        'description' => E::ts('The amount if the drawdown is accepted, 0 otherwise.'),
        'type' => 'Extra',
        'data_type' => 'Money',
        'readonly' => TRUE,
        'nullable' => FALSE,
        'sql_renderer' => fn (array $field, Api4SelectQuery $query) => sprintf(
          '(IF(%s = "accepted", %s, 0))',
          SqlRendererUtil::getFieldSqlName($field, $query, 'status'),
          SqlRendererUtil::getFieldSqlName($field, $query, 'amount')
        ),
      ],
      [
        'name' => 'amount_paid_out',
        'title' => E::ts('Amount Paid Out'),
        'description' => E::ts('The amount if the drawdown is accepted and positive, 0 otherwise.'),
        'type' => 'Extra',
        'data_type' => 'Money',
        'readonly' => TRUE,
        'nullable' => FALSE,
        'sql_renderer' => fn (array $field, Api4SelectQuery $query) => sprintf(
          '(IF(%s = "accepted" AND %2$s > 0, %2$s, 0))',
          SqlRendererUtil::getFieldSqlName($field, $query, 'status'),
          SqlRendererUtil::getFieldSqlName($field, $query, 'amount')
        ),
      ],
      [
        'name' => 'amount_new',
        'title' => E::ts('Amount Open'),
        'description' => E::ts('The amount if the drawdown is unverified, 0 otherwise.'),
        'type' => 'Extra',
        'data_type' => 'Money',
        'readonly' => TRUE,
        'nullable' => FALSE,
        'sql_renderer' => fn (array $field, Api4SelectQuery $query) => sprintf(
          '(IF(%s = "new", %s, 0))',
          SqlRendererUtil::getFieldSqlName($field, $query, 'status'),
          SqlRendererUtil::getFieldSqlName($field, $query, 'amount')
        ),
      ],
      [
        'name' => 'CAN_review',
        'type' => 'Custom',
        'data_type' => 'Boolean',
        'readonly' => TRUE,
        'required' => FALSE,
      ],
    ]);
  }

}
