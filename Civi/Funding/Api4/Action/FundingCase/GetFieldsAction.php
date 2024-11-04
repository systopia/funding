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

namespace Civi\Funding\Api4\Action\FundingCase;

use Civi\Api4\FundingCase;
use Civi\Api4\Generic\DAOGetFieldsAction;
use Civi\Api4\Query\Api4Query;
use Civi\Api4\Query\Api4SelectQuery;
use Civi\Funding\Api4\Query\AliasSqlRenderer;
use Civi\Funding\Api4\Query\Util\SqlRendererUtil;
use Civi\RemoteTools\Api4\Action\Traits\PermissionsGetFieldsActionTrait;
use Civi\RemoteTools\Authorization\PossiblePermissionsLoaderInterface;
use CRM_Funding_ExtensionUtil as E;

final class GetFieldsAction extends DAOGetFieldsAction {

  use PermissionsGetFieldsActionTrait {
    PermissionsGetFieldsActionTrait::getRecords as getRecordsWithPermissions;
  }

  private PossiblePermissionsLoaderInterface $possiblePermissionsLoader;

  public function __construct(PossiblePermissionsLoaderInterface $possiblePermissionsLoader) {
    parent::__construct(FundingCase::getEntityName(), 'getFields');
    $this->possiblePermissionsLoader = $possiblePermissionsLoader;
  }

  /**
   * @phpstan-return array<array<string, array<string, scalar>|array<scalar>|scalar|null>&array{name: string}>
   */
  protected function getRecords(): array {
    return array_merge($this->getRecordsWithPermissions(), [
      [
        'name' => 'transfer_contract_uri',
        'description' => 'URI to download the transfer contract or null if no transfer contract exists.',
        'type' => 'Custom',
        'data_type' => 'String',
        'readonly' => TRUE,
        'nullable' => TRUE,
        'operators' => [],
      ],
      [
        'name' => 'currency',
        'title' => E::ts('Currency'),
        'type' => 'Extra',
        'data_type' => 'String',
        'readonly' => TRUE,
        'sql_renderer' => new AliasSqlRenderer('funding_program_id.currency'),
      ],
      [
        'name' => 'amount_requested',
        'title' => E::ts('Amount Requested'),
        'type' => 'Extra',
        'data_type' => 'Money',
        'readonly' => TRUE,
        'nullable' => FALSE,
        'sql_renderer' => fn () => sprintf('IFNULL(
        (SELECT SUM(ap.amount_requested) FROM civicrm_funding_application_process ap
        WHERE ap.funding_case_id = %s.id)
      , 0)', Api4Query::MAIN_TABLE_ALIAS),
      ],
      [
        'name' => 'amount_paid_out',
        'title' => E::ts('Amount Paid Out'),
        'description' => E::ts('The sum of the amounts of accepted drawdowns.'),
        'type' => 'Extra',
        'data_type' => 'Money',
        'readonly' => TRUE,
        'nullable' => FALSE,
        'sql_renderer' => fn () => sprintf("IFNULL(
        (SELECT SUM(drawdown.amount) FROM civicrm_funding_payout_process payout
        JOIN civicrm_funding_drawdown drawdown ON drawdown.payout_process_id = payout.id
          AND drawdown.status = 'accepted'
        WHERE payout.funding_case_id = %s.id)
      , 0)", Api4Query::MAIN_TABLE_ALIAS),
      ],
      [
        'name' => 'amount_cleared',
        'title' => E::ts('Amount Cleared'),
        'type' => 'Extra',
        'data_type' => 'Money',
        'readonly' => TRUE,
        'nullable' => TRUE,
        'operators' => [],
        // Without sql renderer the query would fail. The actual value is fetched afterward.
        'sql_renderer' => fn () => '(SELECT NULL)',
      ],
      [
        'name' => 'amount_admitted',
        'title' => E::ts('Amount Admitted'),
        'type' => 'Extra',
        'data_type' => 'Money',
        'readonly' => TRUE,
        'nullable' => TRUE,
        // Without sql renderer the query would fail. The actual value is fetched afterward.
        'sql_renderer' => fn () => '(SELECT NULL)',
      ],
      [
        'name' => 'application_process_review_progress',
        'title' => E::ts('Review Progress'),
        'description' => E::ts('The progress of application review in %.'),
        'type' => 'Extra',
        'data_type' => 'Integer',
        'readonly' => TRUE,
        'nullable' => TRUE,
        'sql_renderer' => fn (array $field, Api4SelectQuery $query) => sprintf('
          (
            SELECT
                COUNT(CASE WHEN fap.is_eligible IS NOT NULL THEN 1 END)
              / COUNT(fap.id) 
              * 100
            FROM
              civicrm_funding_application_process AS fap
            WHERE
              fap.funding_case_id = %s
          )', SqlRendererUtil::getFieldSqlName($field, $query, 'id')
        ),
      ],
    ]);
  }

  /**
   * @phpstan-return array<string, string>
   */
  protected function getPossiblePermissions(): array {
    return $this->possiblePermissionsLoader->getFilteredPermissions($this->getEntityName());
  }

}
