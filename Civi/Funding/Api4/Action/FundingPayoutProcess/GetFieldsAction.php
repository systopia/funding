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

namespace Civi\Funding\Api4\Action\FundingPayoutProcess;

use Civi\Api4\FundingPayoutProcess;
use Civi\Api4\Generic\DAOGetFieldsAction;
use Civi\Api4\Query\Api4SelectQuery;
use Civi\Funding\Api4\Query\AliasSqlRenderer;
use Civi\Funding\Api4\Query\Util\SqlRendererUtil;
use CRM_Funding_ExtensionUtil as E;

/**
 * @phpstan-type fieldT array<string, array<string, scalar>|scalar[]|scalar|null>
 */
final class GetFieldsAction extends DAOGetFieldsAction {

  public function __construct() {
    parent::__construct(FundingPayoutProcess::getEntityName(), 'getFields');
  }

  /**
   * @phpstan-return list<fieldT>
   */
  protected function getRecords(): array {
    return array_merge(parent::getRecords(), [
      [
        'name' => 'currency',
        'title' => E::ts('Currency'),
        'type' => 'Extra',
        'data_type' => 'String',
        'readonly' => TRUE,
        'sql_renderer' => new AliasSqlRenderer('funding_case_id.funding_program_id.currency'),
      ],
      [
        'name' => 'amount_accepted',
        'title' => E::ts('Amount Accepted'),
        'description' => E::ts('The sum of the amounts of accepted drawdowns.'),
        'type' => 'Extra',
        'data_type' => 'Money',
        'readonly' => TRUE,
        'nullable' => TRUE,
        'sql_renderer' => fn (array $field, Api4SelectQuery $query) => sprintf(
          'IFNULL((SELECT SUM(d.amount) FROM civicrm_funding_drawdown d
          WHERE d.payout_process_id = %s AND d.status = "accepted"), 0)',
          SqlRendererUtil::getFieldSqlName($field, $query, 'id')
        ),
      ],
      [
        'name' => 'amount_paid_out',
        'title' => E::ts('Amount Paid Out'),
        'description' => E::ts('The sum of the amounts of accepted, positive drawdowns.'),
        'type' => 'Extra',
        'data_type' => 'Money',
        'readonly' => TRUE,
        'nullable' => TRUE,
        'sql_renderer' => fn (array $field, Api4SelectQuery $query) => sprintf(
          'IFNULL((SELECT SUM(d.amount) FROM civicrm_funding_drawdown d
          WHERE d.payout_process_id = %s AND d.status = "accepted" AND d.amount > 0), 0)',
          SqlRendererUtil::getFieldSqlName($field, $query, 'id')
        ),
      ],
      [
        'name' => 'amount_new',
        'title' => E::ts('Amount Open'),
        'description' => E::ts('The sum of the amounts of unverified drawdowns.'),
        'type' => 'Extra',
        'data_type' => 'Money',
        'readonly' => TRUE,
        'nullable' => TRUE,
        'sql_renderer' => fn (array $field, Api4SelectQuery $query) => sprintf(
          'IFNULL((SELECT SUM(d.amount) FROM civicrm_funding_drawdown d
          WHERE d.payout_process_id = %s AND d.status = "new"), 0)',
          SqlRendererUtil::getFieldSqlName($field, $query, 'id')
        ),
      ],
    ]);
  }

}
