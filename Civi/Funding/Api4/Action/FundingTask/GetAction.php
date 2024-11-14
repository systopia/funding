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
use Civi\Api4\Generic\Result;
use Civi\Funding\ActivityStatusTypes;
use Civi\Funding\ActivityTypeNames;
use Civi\Funding\Api4\Action\FundingCase\AbstractReferencingDAOGetAction;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Query\CompositeCondition;
use Civi\RemoteTools\RequestContext\RequestContextInterface;

/**
 * @method int|null getStatusType()
 * @method $this setStatusType(int $statusType)
 */
final class GetAction extends AbstractReferencingDAOGetAction {

  /**
   * @var int
   */
  protected ?int $statusType = NULL;

  public function __construct(
    Api4Interface $api4,
    FundingCaseManager $fundingCaseManager,
    RequestContextInterface $requestContext
  ) {
    parent::__construct(
      Activity::getEntityName(),
      $api4,
      $fundingCaseManager,
      $requestContext
    );
    $this->_fundingCaseIdFieldName = 'funding_case_task.funding_case_id';
  }

  public function _run(Result $result): void {
    if ([] === $this->select) {
      $this->addSelect(
        '*',
        'activity_type_id:name',
        'status_id:name',
        'funding_case_task.*',
        'funding_application_process_task.*',
        'funding_clearing_process_task.*'
      );
    }

    if ([] === $this->getWhere()) {
      // If there's no filter given we restrict access to incomplete tasks for
      // performance reasons.
      $this->statusType ??= ActivityStatusTypes::INCOMPLETE;
    }

    if (NULL !== $this->statusType) {
      $this->addJoin('OptionValue as _ov', 'INNER', NULL,
        ['_ov.filter', '=', $this->statusType],
        ['_ov.value', '=', 'status_id'],
        ['_ov.option_group_id.name', '=', '"activity_status"']
      );
    }

    $this->addWhere('activity_type_id:name', 'IN', ActivityTypeNames::getTasks());

    $this->addClause('OR',
      ['assignee_contact_id', 'IS NULL'],
      ['assignee_contact_id', '=', $this->_requestContext->getContactId()],
    );

    $this->addSelect(
      'funding_case_task.funding_case_id',
      'funding_case_task.required_permissions',
      '_pc.permissions',
    );

    // Permission check. If _pc.permissions is NULL, check is performed in handleRecord().
    $this->addClause('OR',
      ['_pc.permissions', 'IS NULL'],
      CompositeCondition::new('AND',
        Comparison::new('_pc.permissions', '!=', NULL),
        CompositeCondition::new('OR',
          Comparison::new('funding_case_task.required_permissions', '=', NULL),
          Comparison::new('FUNDING_JSON_OVERLAPS(_pc.permissions, funding_case_task.required_permissions)', '=', TRUE),
        )
      )->toArray(),
    );

    parent::_run($result);
  }

  protected function handleRecord(array &$record): bool {
    $permissions = $record['_pc.permissions'];
    unset($record['_pc.permissions']);
    if (NULL !== $permissions) {
      // Overlap was checked in SQL.
      return TRUE;
    }

    // @phpstan-ignore argument.type
    $fundingCase = $this->_fundingCaseManager->get($record['funding_case_task.funding_case_id']);
    if (NULL === $fundingCase) {
      return FALSE;
    }

    /** @phpstan-var list<string>|null $requiredPermissions */
    $requiredPermissions = $record['funding_case_task.required_permissions'];

    return NULL === $requiredPermissions
     || [] !== array_intersect($fundingCase->getPermissions(), $requiredPermissions);
  }

}
