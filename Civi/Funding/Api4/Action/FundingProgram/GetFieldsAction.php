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

namespace Civi\Funding\Api4\Action\FundingProgram;

use Civi\Api4\FundingProgram;
use Civi\Api4\Generic\DAOGetFieldsAction;
use Civi\Api4\Query\Api4SelectQuery;
use Civi\Funding\Api4\Query\Util\SqlRendererUtil;
use Civi\RemoteTools\Api4\Action\Traits\PermissionsGetFieldsActionTrait;
use Civi\RemoteTools\Authorization\PossiblePermissionsLoaderInterface;
use CRM_Funding_ExtensionUtil as E;

final class GetFieldsAction extends DAOGetFieldsAction {

  use PermissionsGetFieldsActionTrait {
    PermissionsGetFieldsActionTrait::getRecords as traitGetRecords;
  }

  private PossiblePermissionsLoaderInterface $possiblePermissionsLoader;

  public function __construct(PossiblePermissionsLoaderInterface $possiblePermissionsLoader) {
    parent::__construct(FundingProgram::getEntityName(), 'getFields');
    $this->possiblePermissionsLoader = $possiblePermissionsLoader;
  }

  /**
   * @phpstan-return array<array<string, array<string, scalar>|array<scalar>|scalar|null>&array{name: string}>
   */
  protected function getRecords(): array {
    $fields = $this->traitGetRecords();

    $fields[] = [
      'name' => 'amount_eligible',
      'title' => E::ts('Amount Eligible'),
      'description' => E::ts('The sum of the amounts requested of eligible applications in the funding program.'),
      'type' => 'Extra',
      'data_type' => 'Money',
      'readonly' => TRUE,
      'nullalbe' => FALSE,
      'sql_renderer' => fn (array $field, Api4SelectQuery $query) => sprintf('IFNULL(
        (SELECT SUM(amount_requested) FROM civicrm_funding_application_process WHERE
          is_eligible = 1 AND
          funding_case_id IN (SELECT id FROM civicrm_funding_case WHERE funding_program_id = %s))
      , 0)', SqlRendererUtil::getFieldSqlName($field, $query, 'id')),
    ];

    $fields[] = [
      'name' => 'amount_approved',
      'title' => E::ts('Amount Approved'),
      'type' => 'Extra',
      'data_type' => 'Money',
      'readonly' => TRUE,
      'nullable' => FALSE,
      'sql_renderer' => fn (array $field, Api4SelectQuery $query) => sprintf('IFNULL(
        (SELECT SUM(amount_approved) FROM civicrm_funding_case WHERE funding_program_id = %s)
      , 0)', SqlRendererUtil::getFieldSqlName($field, $query, 'id')),
    ];

    $fields[] = [
      'name' => 'amount_available',
      'title' => E::ts('Amount Available'),
      'description' => E::ts('The difference between the budget and the amount approved.'),
      'type' => 'Extra',
      'data_type' => 'Money',
      'readonly' => TRUE,
      'sql_renderer' => fn (array $field, Api4SelectQuery $query) => sprintf('%s - IFNULL(
        (SELECT SUM(amount_approved) FROM civicrm_funding_case WHERE funding_program_id = %s)
      , 0)', SqlRendererUtil::getFieldSqlName($field, $query, 'budget'),
        SqlRendererUtil::getFieldSqlName($field, $query, 'id')),
    ];

    $fields[] = [
      'name' => 'amount_drawdowns_accepted',
      'title' => E::ts('Amount Drawdowns Accepted'),
      'description' => E::ts('The sum of the amounts of accepted drawdowns.'),
      'type' => 'Extra',
      'data_type' => 'Money',
      'readonly' => TRUE,
      'nullable' => FALSE,
      'sql_renderer' => fn (array $field, Api4SelectQuery $query) => sprintf("IFNULL(
        (SELECT SUM(drawdown.amount) FROM civicrm_funding_case c
        JOIN civicrm_funding_payout_process payout ON payout.funding_case_id = c.id
        JOIN civicrm_funding_drawdown drawdown ON drawdown.payout_process_id = payout.id
          AND drawdown.status = 'accepted'
        WHERE c.funding_program_id = %s)
      , 0)", SqlRendererUtil::getFieldSqlName($field, $query, 'id')),
    ];

    $fields[] = [
      'name' => 'amount_paid_out',
      'title' => E::ts('Amount Paid Out'),
      'description' => E::ts('The sum of the amounts of accepted, positive drawdowns.'),
      'type' => 'Extra',
      'data_type' => 'Money',
      'readonly' => TRUE,
      'nullable' => FALSE,
      'sql_renderer' => fn (array $field, Api4SelectQuery $query) => sprintf("IFNULL(
        (SELECT SUM(drawdown.amount) FROM civicrm_funding_case c
        JOIN civicrm_funding_payout_process payout ON payout.funding_case_id = c.id
        JOIN civicrm_funding_drawdown drawdown ON drawdown.payout_process_id = payout.id
          AND drawdown.status = 'accepted' AND drawdown.amount > 0
        WHERE c.funding_program_id = %s)
      , 0)", SqlRendererUtil::getFieldSqlName($field, $query, 'id')),
    ];

    $fields[] = [
      'name' => 'amount_cleared',
      'title' => E::ts('Amount Cleared'),
      'type' => 'Extra',
      'data_type' => 'Money',
      'readonly' => TRUE,
      'nullable' => TRUE,
      'operators' => [],
      // Without sql renderer the query would fail. The actual value is fetched afterward.
      'sql_renderer' => fn () => '(SELECT NULL)',
    ];

    $fields[] = [
      'name' => 'amount_admitted',
      'title' => E::ts('Amount Admitted'),
      'type' => 'Extra',
      'data_type' => 'Money',
      'readonly' => TRUE,
      'nullable' => TRUE,
      'operators' => [],
      // Without sql renderer the query would fail. The actual value is fetched afterward.
      'sql_renderer' => fn () => '(SELECT NULL)',
    ];

    return $fields;
  }

  /**
   * @phpstan-return array<string, string>
   */
  protected function getPossiblePermissions(): array {
    return $this->possiblePermissionsLoader->getFilteredPermissions($this->getEntityName());
  }

}
