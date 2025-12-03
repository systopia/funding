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
use Civi\Api4\Query\Api4SelectQuery;
use Civi\Funding\Api4\Action\FundingClearingProcess\Query\ClearingProcessQuery;
use Civi\Funding\Api4\Action\Traits\PossiblePermissionsLoaderTrait;
use Civi\Funding\Api4\Query\AliasSqlRenderer;
use Civi\Funding\Api4\Query\Util\SqlRendererUtil;
use Civi\Funding\Api4\Traits\PermissionsGetFieldsActionTrait;
use Civi\Funding\Permission\PossiblePermissionsLoaderInterface;
use CRM_Funding_ExtensionUtil as E;

final class GetFieldsAction extends DAOGetFieldsAction {

  use PermissionsGetFieldsActionTrait {
    PermissionsGetFieldsActionTrait::getRecords as getRecordsWithPermissions;
  }

  use PossiblePermissionsLoaderTrait;

  public function __construct(?PossiblePermissionsLoaderInterface $possiblePermissionsLoader = NULL) {
    parent::__construct(FundingCase::getEntityName(), 'getFields');
    $this->_possiblePermissionsLoader = $possiblePermissionsLoader;
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
        'sql_renderer' => fn (array $field, Api4SelectQuery $query) => sprintf('IFNULL(
        (SELECT SUM(ap.amount_requested) FROM civicrm_funding_application_process ap
        WHERE ap.funding_case_id = %s AND ap.is_withdrawn = FALSE AND ap.is_rejected = FALSE)
      , 0)', SqlRendererUtil::getFieldSqlName($field, $query, 'id')),
      ],
      [
        'name' => 'amount_drawdowns',
        'title' => E::ts('Amount Drawdowns'),
        'description' => E::ts('The sum of the amounts of drawdowns.'),
        'type' => 'Extra',
        'data_type' => 'Money',
        'readonly' => TRUE,
        'nullable' => FALSE,
        'sql_renderer' => fn (array $field, Api4SelectQuery $query) => sprintf('IFNULL(
        (SELECT SUM(drawdown.amount) FROM civicrm_funding_payout_process payout
        JOIN civicrm_funding_drawdown drawdown ON drawdown.payout_process_id = payout.id %s
        WHERE payout.funding_case_id = %s)
        , 0)', $this->buildDrawdownDateClause($query), SqlRendererUtil::getFieldSqlName($field, $query, 'id')),
      ],
      [
        'name' => 'amount_drawdowns_open',
        'title' => E::ts('Amount Drawdowns Open'),
        'description' => E::ts('The sum of the amounts of new (unreviewed) drawdowns.'),
        'type' => 'Extra',
        'data_type' => 'Money',
        'readonly' => TRUE,
        'nullable' => FALSE,
        'sql_renderer' => fn (array $field, Api4SelectQuery $query) => sprintf("IFNULL(
        (SELECT SUM(drawdown.amount) FROM civicrm_funding_payout_process payout
        JOIN civicrm_funding_drawdown drawdown ON drawdown.payout_process_id = payout.id %s
          AND drawdown.status = 'new'
        WHERE payout.funding_case_id = %s)
      , 0)", $this->buildDrawdownDateClause($query), SqlRendererUtil::getFieldSqlName($field, $query, 'id')),
      ],
      [
        'name' => 'amount_drawdowns_accepted',
        'title' => E::ts('Amount Drawdowns Accepted'),
        'description' => E::ts('The sum of the amounts of accepted drawdowns.'),
        'type' => 'Extra',
        'data_type' => 'Money',
        'readonly' => TRUE,
        'nullable' => FALSE,
        'sql_renderer' => fn (array $field, Api4SelectQuery $query) => sprintf("IFNULL(
        (SELECT SUM(drawdown.amount) FROM civicrm_funding_payout_process payout
        JOIN civicrm_funding_drawdown drawdown ON drawdown.payout_process_id = payout.id %s
          AND drawdown.status = 'accepted'
        WHERE payout.funding_case_id = %s)
      , 0)", $this->buildDrawdownDateClause($query), SqlRendererUtil::getFieldSqlName($field, $query, 'id')),
      ],
      [
        'name' => 'amount_paid_out',
        'title' => E::ts('Amount Paid Out'),
        'description' => E::ts('The sum of the amounts of accepted, positive drawdowns.'),
        'type' => 'Extra',
        'data_type' => 'Money',
        'readonly' => TRUE,
        'nullable' => FALSE,
        'sql_renderer' => fn (array $field, Api4SelectQuery $query) => sprintf("IFNULL(
        (SELECT SUM(drawdown.amount) FROM civicrm_funding_payout_process payout
        JOIN civicrm_funding_drawdown drawdown ON drawdown.payout_process_id = payout.id
          AND drawdown.status = 'accepted' AND drawdown.amount > 0
        WHERE payout.funding_case_id = %s)
      , 0)", SqlRendererUtil::getFieldSqlName($field, $query, 'id')),
      ],
      [
        'name' => 'withdrawable_funds',
        'title' => E::ts('Withdrawable Funds'),
        'description' => E::ts('The difference between the amount approved and the amount of accepted drawdowns.'),
        'type' => 'Extra',
        'data_type' => 'Money',
        'readonly' => TRUE,
        // NULL if funding case is not approved (yet).
        'nullable' => TRUE,
        // Note: Cannot be used in aggregation functions.
        'sql_renderer' => fn (array $field, Api4SelectQuery $query) => sprintf(
          '(SELECT %s - amount_drawdowns_accepted)',
          SqlRendererUtil::getFieldSqlName($field, $query, 'amount_approved'),
        ),
      ],
      [
        'name' => 'amount_cleared',
        'title' => E::ts('Amount Cleared'),
        'type' => 'Extra',
        'data_type' => 'Money',
        'readonly' => TRUE,
        'nullable' => TRUE,
        'operators' => [],
        'sql_renderer' => fn (array $field, Api4SelectQuery $query) => ClearingProcessQuery::amountCleared(
          sprintf('(SELECT cp.id from civicrm_funding_clearing_process cp
            JOIN civicrm_funding_application_process ap ON ap.id = cp.application_process_id
            AND ap.funding_case_id = %s)',
            SqlRendererUtil::getFieldSqlName($field, $query, 'id')
          ),
          'IN'
        ),
      ],
      [
        'name' => 'amount_admitted',
        'title' => E::ts('Amount Admitted'),
        'type' => 'Extra',
        'data_type' => 'Money',
        'readonly' => TRUE,
        'nullable' => TRUE,
        'sql_renderer' => fn (array $field, Api4SelectQuery $query) => ClearingProcessQuery::amountAdmitted(
          sprintf('(SELECT cp.id from civicrm_funding_clearing_process cp
            JOIN civicrm_funding_application_process ap ON ap.id = cp.application_process_id
            AND ap.funding_case_id = %s)',
            SqlRendererUtil::getFieldSqlName($field, $query, 'id')
          ),
          'IN'
        ),
      ],
      [
        'name' => 'application_process_review_progress',
        'title' => E::ts('Review Progress'),
        'description' => E::ts('The progress of application review in percent.'),
        'type' => 'Extra',
        'data_type' => 'Integer',
        'readonly' => TRUE,
        'nullable' => TRUE,
        'sql_renderer' => fn (array $field, Api4SelectQuery $query) => sprintf('
          (
            SELECT IFNULL(
                COUNT(CASE WHEN fap.is_eligible IS NOT NULL THEN 1 END)
              / COUNT(CASE WHEN fap.is_in_work = FALSE THEN 1 END)
              * 100, 100)
            FROM
              civicrm_funding_application_process AS fap
            WHERE
              fap.funding_case_id = %s
          )', SqlRendererUtil::getFieldSqlName($field, $query, 'id')
        ),
      ],
      [
        'name' => 'drawdown_acception_date',
        'title' => E::ts('Drawdown Acception Date'),
        'description' => E::ts('The date of the drawdown acception. Note: Cannot be used in sub-clauses.'),
        'type' => 'Filter',
        'data_type' => 'Timestamp',
        'input_type' => 'Date',
      ],
      [
        'name' => 'drawdown_creation_date',
        'title' => E::ts('Drawdown Creation Date'),
        'description' => E::ts('The date of the drawdown creation. Note: Cannot be used in sub-clauses.'),
        'type' => 'Filter',
        'data_type' => 'Timestamp',
        'input_type' => 'Date',
      ],
    ]);
  }

  /**
   * @phpstan-return array<string, string>
   */
  protected function getPossiblePermissions(): array {
    return $this->getPossiblePermissionsLoader()->getFilteredPermissions($this->getEntityName());
  }

  private function buildDrawdownDateClause(Api4SelectQuery $query): string {
    $clauses = [];

    $acceptionDateOperator = GetAction::getDrawdownAcceptionDateOperator();
    $acceptionDateValue = GetAction::getDrawdownAcceptionDateValue();
    if (NULL !== $acceptionDateOperator) {
      if (NULL === $acceptionDateValue) {
        $clauses[] = "drawdown.acception_date $acceptionDateOperator";
      }
      else {
        $clauses[] = "drawdown.acception_date $acceptionDateOperator '$acceptionDateValue'";
      }
    }

    $creationDateOperator = GetAction::getDrawdownCreationDateOperator();
    $creationDateValue = GetAction::getDrawdownCreationDateValue();
    if (NULL !== $creationDateOperator) {
      if (NULL === $creationDateValue) {
        $clauses[] = "drawdown.creation_date $creationDateOperator";
      }
      else {
        $clauses[] = "drawdown.creation_date $creationDateOperator '$creationDateValue'";
      }
    }

    if ([] === $clauses) {
      return '';
    }

    return ' AND ' . implode(' AND ', $clauses);
  }

}
