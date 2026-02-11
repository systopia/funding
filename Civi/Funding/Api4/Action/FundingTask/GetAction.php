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

use Civi\API\Exception\UnauthorizedException;
use Civi\Api4\Activity;
use Civi\Api4\Generic\Result;
use Civi\Funding\ActivityStatusTypes;
use Civi\Funding\ActivityTypeNames;
use Civi\Funding\Api4\Action\FundingCase\AbstractReferencingDAOGetAction;
use Civi\Funding\Api4\Util\WhereUtil;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Civi\RemoteTools\Api4\Query\CompositeCondition;
use Civi\RemoteTools\RequestContext\RequestContextInterface;
use Webmozart\Assert\Assert;

/**
 * @method bool getIgnoreTaskPermissions()
 * @method $this setIgnoreTaskPermissions(bool $ignoreTaskPermissions)
 * @method int|null getStatusType()
 * @method $this setStatusType(int $statusType)
 * @method bool|null getUseAssigneeFilter()
 * @method $this setAssigneeFilter(bool|null $assigneeFilter)
 */
final class GetAction extends AbstractReferencingDAOGetAction {

  /**
   * @var bool
   */
  protected bool $ignoreTaskPermissions = FALSE;

  /**
   * @var int
   */
  protected ?int $statusType = NULL;

  /**
   * @var bool|null
   *
   * If assignee filter is enabled, only tasks with the current contact as
   * assignee or no assignee are returned.
   *
   * If NULL, assignee filter is used on remote requests.
   */
  protected ?bool $useAssigneeFilter = NULL;

  public function __construct(
    ?Api4Interface $api4 = NULL,
    ?FundingCaseManager $fundingCaseManager = NULL,
    ?RequestContextInterface $requestContext = NULL
  ) {
    parent::__construct(
      Activity::getEntityName(),
      $api4,
      $fundingCaseManager,
      $requestContext
    );
    $this->_fundingCaseIdFieldName = 'funding_case_task.funding_case_id';
  }

  // phpcs:disable Generic.Metrics.CyclomaticComplexity.TooHigh
  public function setDefaultWhereClause(): void {
  // phpcs:enable
    $this->assertUseAssigneeFilter();

    foreach ($this->where as $index => $clause) {
      if ('ignore_task_permissions' === $clause[0]) {
        Assert::same('=', $clause[1], 'Only "=" is allowed as operator for "ignore_task_permissions"');
        $this->ignoreTaskPermissions = $clause[2];
        unset($this->where[$index]);
        $this->where = array_values($this->where);
        break;
      }
    }

    $this->assertIgnoreTaskPermissions();

    if ([] === $this->getWhere()) {
      // If there's no filter given we restrict access to incomplete tasks for
      // performance reasons.
      $this->statusType ??= ActivityStatusTypes::INCOMPLETE;
    }
    elseif (!$this->getRequestContext()->isRemote()) {
      // The filter ['assignee_contact_id', 'CONTAINS', 'user_contact_id']
      // doesn't work. Therefor, we replace user_contact_id at this point.
      $this->where = WhereUtil::replaceField(
        $this->where,
        ['assignee_contact_id' => 'assignee_contact_id'],
        ['user_contact_id' => $this->getRequestContext()->getLoggedInContactId()]
      );
    }

    parent::setDefaultWhereClause();

    if (NULL !== $this->statusType) {
      $this->addWhere('status_type_id', '=', $this->statusType);
    }

    // The field status_type_id is mapped to _ov.filter. This way we don't have
    // to influence execution of Activity.get (In any way it wouldn't result in
    // "nice" code.)
    if ($this->_isFieldSelected('status_type_id', 'status_type_id:label', 'status_type_id:name')
        // Note $this->_whereContains() has a bug before before CiviCRM 6.5.
        // https://github.com/civicrm/civicrm-core/pull/32974
      || WhereUtil::containsField($this->where, 'status_type_id', 'status_type_id:label', 'status_type_id:name')
    ) {
      $this->addJoin('OptionValue AS _ov', 'INNER', NULL,
        ['_ov.value', '=', 'status_id'],
        ['_ov.option_group_id.name', '=', '"activity_status"']
      );

      $newSelect = array_filter(
        $this->select,
        fn (string $field) => !in_array($field, ['status_type_id', 'status_type_id:label', 'status_type_id:name'], TRUE)
      );
      if ($newSelect !== $this->select) {
        $this->select = $newSelect;
        $this->addSelect('_ov.filter');
      }

      $statusTypeLabels = GetFieldsAction::getStatusTypeLabels();
      $this->where = WhereUtil::replaceField($this->where, [
        'status_type_id' => '_ov.filter',
        'status_type_id:name' => '_ov.filter',
        'status_type_id:label' => '_ov.filter',
      ], array_combine(array_values($statusTypeLabels), array_keys($statusTypeLabels)));

      if (isset($this->orderBy['status_type_id'])) {
        $this->orderBy['_ov.filter'] = $this->orderBy['status_type_id'];
        unset($this->orderBy['status_type_id']);
      }
      if (isset($this->orderBy['status_type_id:name'])) {
        $this->orderBy['_ov.filter'] = $this->orderBy['status_type_id:name'];
        unset($this->orderBy['status_type_id:name']);
      }
      if (isset($this->orderBy['status_type_id:label'])) {
        // Depending on the field labels this might not lead to the expected result...
        $this->orderBy['_ov.filter'] = $this->orderBy['status_type_id:label'];
        unset($this->orderBy['status_type_id:label']);
      }
    }

    $this->addWhere('activity_type_id:name', 'IN', ActivityTypeNames::getTasks());

    if ($this->isUseAssigneeFilter()) {
      $this->addClause(
        'OR',
        ['assignee_contact_id', 'IS NULL'],
        ['assignee_contact_id', '=', $this->getRequestContext()->getContactId()],
      );
    }

    if (!$this->ignoreCasePermissions && !$this->ignoreTaskPermissions) {
      $this->addSelect(
        'funding_case_task.funding_case_id',
        'funding_case_task.required_permissions',
        '_pc.permissions',
      );

      // Permission check. If _pc.permissions is NULL, check is performed in handleRecord().
      $this->addClause(
        'OR',
        ['_pc.permissions', 'IS NULL'],
        CompositeCondition::new(
          'AND',
          Comparison::new('_pc.permissions', '!=', NULL),
          CompositeCondition::new(
            'OR',
            Comparison::new('funding_case_task.required_permissions', '=', NULL),
            // CiviCRM doesn't persist NULL, but an empty string.
            Comparison::new('funding_case_task.required_permissions', '=', ''),
            Comparison::new(
              'FUNDING_JSON_OVERLAPS(_pc.permissions, funding_case_task.required_permissions)',
              '=',
              TRUE
            ),
          )
        )->toArray(),
      );
    }
  }

  public function _run(Result $result): void {
    if ([] === $this->select) {
      $this->addSelect(
        '*',
        'activity_type_id:name',
        'status_id:name',
        'funding_case_task.*',
        'funding_application_process_task.*',
        'funding_clearing_process_task.*',
        'funding_payout_process_task.*',
        'funding_drawdown_task.*',
      );
    }

    parent::_run($result);

    if (in_array('_ov.filter', $this->select, TRUE)) {
      /** @phpstan-var array<string, mixed> $record */
      foreach ($result as &$record) {
        if (!array_key_exists('_ov.filter', $record)) {
          continue;
        }

        if ($this->isFieldExplicitlySelected('status_type_id')) {
          $record['status_type_id'] = $record['_ov.filter'];
        }
        if ($this->isFieldExplicitlySelected('status_type_id:name')) {
          $record['status_type_id:name'] = $record['_ov.filter'];
        }
        if ($this->isFieldExplicitlySelected('status_type_id:label')) {
          $this->entityFields();
          // @phpstan-ignore offsetAccess.invalidOffset
          $record['status_type_id:label'] = GetFieldsAction::getStatusTypeLabels()[$record['_ov.filter']]
            ?? $record['_ov.filter'];
        }
        unset($record['_ov.filter']);
      }
    }
  }

  protected function handleRecord(array &$record): bool {
    if ($this->ignoreTaskPermissions) {
      return TRUE;
    }

    $permissions = $record['_pc.permissions'];
    unset($record['_pc.permissions']);
    if (NULL !== $permissions) {
      // Overlap was checked in SQL.
      return TRUE;
    }

    if (!isset($record['funding_case_task.funding_case_id'])) {
      return FALSE;
    }

    // @phpstan-ignore argument.type
    $fundingCase = $this->getFundingCaseManager()->get($record['funding_case_task.funding_case_id']);
    if (NULL === $fundingCase) {
      return FALSE;
    }

    if (is_string($record['funding_case_task.required_permissions'])) {
      $record['funding_case_task.required_permissions'] =
        '' === $record['funding_case_task.required_permissions'] ? NULL
        : json_decode($record['funding_case_task.required_permissions'], TRUE, 2, JSON_THROW_ON_ERROR);
    }
    /** @var list<string>|null $requiredPermissions */
    $requiredPermissions = $record['funding_case_task.required_permissions'];

    return NULL === $requiredPermissions
     || [] !== array_intersect($fundingCase->getPermissions(), $requiredPermissions);
  }

  /**
   * @throws \Civi\API\Exception\UnauthorizedException
   */
  private function assertIgnoreTaskPermissions(): void {
    if (FALSE === $this->ignoreTaskPermissions &&
      $this->getCheckPermissions() && $this->getRequestContext()->isRemote()
    ) {
      throw new UnauthorizedException('Ignoring task permissions is not allowed');
    }
  }

  /**
   * @throws \Civi\API\Exception\UnauthorizedException
   */
  private function assertUseAssigneeFilter(): void {
    // Only allow to disable assignee filter on internal requests with check
    // permissions disabled or if contact has administer permission.
    if (FALSE === $this->useAssigneeFilter &&
      $this->getCheckPermissions() && !\CRM_Core_Permission::check('administer CiviCRM')
    ) {
      throw new UnauthorizedException('Disabling assignee filter is not allowed');
    }
  }

  private function isUseAssigneeFilter(): bool {
    return TRUE === $this->useAssigneeFilter
      || (NULL === $this->useAssigneeFilter && $this->getRequestContext()->isRemote());
  }

}
