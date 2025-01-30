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

namespace Civi\Funding\Api4\Action\FundingClearingProcess;

use Civi\Api4\FundingClearingProcess;
use Civi\Api4\Generic\DAOGetFieldsAction;
use Civi\Api4\Query\Api4SelectQuery;
use Civi\Funding\Api4\Query\AliasSqlRenderer;
use Civi\Funding\Api4\Query\Util\SqlRendererUtil;
use Civi\Funding\Api4\Util\ContactUtil;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\ClearingProcess\ClearingProcessManager;
use Civi\Funding\ClearingProcess\ClearingProcessPermissions;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\Permission\FundingCase\FundingCaseContactsLoaderInterface;
use CRM_Funding_ExtensionUtil as E;

/**
 * @phpstan-type fieldsT array<array<string, array<string, scalar>|array<scalar>|scalar|null>&array{name: string}>
 */
final class GetFieldsAction extends DAOGetFieldsAction {

  private ApplicationProcessManager $applicationProcessManager;

  private ClearingProcessManager $clearingProcessManager;

  private FundingCaseContactsLoaderInterface $contactsLoader;

  private FundingCaseManager $fundingCaseManager;

  private bool $fundingCaseLoaded = FALSE;

  private ?FundingCaseEntity $fundingCase = NULL;

  public function __construct(
    ApplicationProcessManager $applicationProcessManager,
    ClearingProcessManager $clearingProcessManager,
    FundingCaseContactsLoaderInterface $contactsLoader,
    FundingCaseManager $fundingCaseManager
  ) {
    parent::__construct(FundingClearingProcess::getEntityName(), 'getFields');
    $this->applicationProcessManager = $applicationProcessManager;
    $this->clearingProcessManager = $clearingProcessManager;
    $this->contactsLoader = $contactsLoader;
    $this->fundingCaseManager = $fundingCaseManager;
  }

  /**
   * @phpstan-return fieldsT
   *
   * @throws \CRM_Core_Exception
   */
  protected function getRecords(): array {
    $fields = parent::getRecords();
    foreach ($fields as &$field) {
      if ('reviewer_calc_contact_id' === $field['name']) {
        $field['options'] = $this->getReviewerContactOptions(ClearingProcessPermissions::REVIEW_CALCULATIVE);
      }
      elseif ('reviewer_cont_contact_id' === $field['name']) {
        $field['options'] = $this->getReviewerContactOptions(ClearingProcessPermissions::REVIEW_CONTENT);
      }
    }

    $fields[] = [
      'name' => 'currency',
      'title' => E::ts('Currency'),
      'type' => 'Extra',
      'data_type' => 'String',
      'readonly' => TRUE,
      'sql_renderer' => new AliasSqlRenderer('application_process_id.funding_case_id.funding_program_id.currency'),
    ];

    $recordedCostsSql = 'IFNULL((SELECT SUM(item.amount) FROM civicrm_funding_clearing_cost_item item
        WHERE item.clearing_process_id = %s AND item.status != "rejected"), 0)';
    $fields[] = [
      'name' => 'amount_recorded_costs',
      'title' => E::ts('Amount Recorded Costs'),
      'description' => E::ts('The sum of the amounts recorded for costs that are not rejected.'),
      'type' => 'Extra',
      'data_type' => 'Money',
      'readonly' => TRUE,
      'nullable' => FALSE,
      'sql_renderer' => fn (array $field, Api4SelectQuery $query) => sprintf(
        $recordedCostsSql,
        SqlRendererUtil::getFieldSqlName($field, $query, 'id')
      ),
    ];

    $recordedResourcesSql = 'IFNULL(
        (SELECT SUM(item.amount) FROM civicrm_funding_clearing_resources_item item
          WHERE item.clearing_process_id = %s AND item.status != "rejected"), 0)';
    $fields[] = [
      'name' => 'amount_recorded_resources',
      'title' => E::ts('Amount Recorded Resources'),
      'description' => E::ts('The sum of the amounts recorded for resources that are not rejected.'),
      'type' => 'Extra',
      'data_type' => 'Money',
      'readonly' => TRUE,
      'nullable' => FALSE,
      'sql_renderer' => fn (array $field, Api4SelectQuery $query) => sprintf(
        $recordedResourcesSql,
        SqlRendererUtil::getFieldSqlName($field, $query, 'id')
      ),
    ];

    $admittedCostsSql = 'IFNULL(
        (SELECT SUM(item.amount_admitted) FROM civicrm_funding_clearing_cost_item item
        WHERE item.clearing_process_id = %s), 0)';
    $fields[] = [
      'name' => 'amount_admitted_costs',
      'title' => E::ts('Amount Admitted Costs'),
      'description' => E::ts('The sum of the amounts admitted for costs.'),
      'type' => 'Extra',
      'data_type' => 'Money',
      'readonly' => TRUE,
      'nullable' => FALSE,
      'sql_renderer' => fn (array $field, Api4SelectQuery $query) => sprintf(
        $admittedCostsSql,
        SqlRendererUtil::getFieldSqlName($field, $query, 'id')
      ),
    ];

    $admittedResourcesSql = 'IFNULL(
        (SELECT SUM(item.amount_admitted) FROM civicrm_funding_clearing_resources_item item
        WHERE item.clearing_process_id = %s), 0)';
    $fields[] = [
      'name' => 'amount_admitted_resources',
      'title' => E::ts('Amount Admitted Resources'),
      'description' => E::ts('The sum of the amounts admitted for resources.'),
      'type' => 'Extra',
      'data_type' => 'Money',
      'readonly' => TRUE,
      'nullable' => FALSE,
      'sql_renderer' => fn(array $field, Api4SelectQuery $query) => sprintf(
        $admittedResourcesSql,
        SqlRendererUtil::getFieldSqlName($field, $query, 'id')
      ),
    ];

    $fields[] = [
      'name' => 'amount_cleared',
      'title' => E::ts('Amount Cleared'),
      'description' => E::ts(
        'The sum of the amounts recorded for costs minus the sum of the amounts recorded for resources.'
      ),
      'type' => 'Extra',
      'data_type' => 'Money',
      'readonly' => TRUE,
      'nullable' => FALSE,
      'sql_renderer' => fn (array $field, Api4SelectQuery $query) => sprintf(
        '(SELECT %s - %s)',
        sprintf($recordedCostsSql, SqlRendererUtil::getFieldSqlName($field, $query, 'id')),
        sprintf($recordedResourcesSql, SqlRendererUtil::getFieldSqlName($field, $query, 'id'))
      ),
    ];

    $fields[] = [
      'name' => 'amount_admitted',
      'title' => E::ts('Amount Admitted'),
      'description' => E::ts(
        'The sum of the amounts admitted for costs minus the sum of the amounts admitted for resources.'
      ),
      'type' => 'Extra',
      'data_type' => 'Money',
      'readonly' => TRUE,
      'nullable' => FALSE,
      'sql_renderer' => fn (array $field, Api4SelectQuery $query) => sprintf(
        '(SELECT %s - %s)',
        sprintf($admittedCostsSql, SqlRendererUtil::getFieldSqlName($field, $query, 'id')),
        sprintf($admittedResourcesSql, SqlRendererUtil::getFieldSqlName($field, $query, 'id'))
      ),
    ];

    return $fields;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function getFundingCaseFromValues(): ?FundingCaseEntity {
    if (!$this->fundingCaseLoaded) {
      if (isset($this->values['id'])) {
        $clearingProcess = $this->clearingProcessManager->get($this->values['id']);
        if (NULL !== $clearingProcess) {
          $applicationProcess = $this->applicationProcessManager->get($clearingProcess->getApplicationProcessId());
          $this->fundingCase = NULL === $applicationProcess ? NULL
            : $this->fundingCaseManager->get($applicationProcess->getFundingCaseId());
        }
      }
      $this->fundingCaseLoaded = TRUE;
    }

    return $this->fundingCase;
  }

  /**
   * @param string $permission
   *
   * @phpstan-return array<int, string>|bool
   *
   * @throws \CRM_Core_Exception
   */
  private function getReviewerContactOptions(string $permission) {
    if (FALSE === $this->loadOptions) {
      // Show contact selection field in Afforms.
      return FALSE;
    }

    $fundingCase = $this->getFundingCaseFromValues();
    if (NULL === $fundingCase) {
      return TRUE;
    }

    $contacts = $this->contactsLoader->getContactsWithAnyPermission($fundingCase, [$permission]);

    /** @phpstan-ignore-next-line */
    return array_map(fn (array $contact) => ContactUtil::getDisplayName($contact), $contacts);
  }

}
