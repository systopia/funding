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
use Civi\Api4\FundingClearingProcess;
use Civi\Api4\Generic\DAOGetAction;
use Civi\Api4\Generic\Result;
use Civi\Core\CiviEventDispatcherInterface;
use Civi\Funding\Api4\Action\Traits\IsFieldSelectedTrait;
use Civi\Funding\Api4\Util\FundingCasePermissionsUtil;
use Civi\Funding\Api4\Util\WhereUtil;
use Civi\Funding\Event\FundingCase\GetPermissionsEvent;
use Civi\Funding\FundingCase\FundingCasePermissionsCacheManager;
use Civi\Funding\FundingCase\TransferContractRouter;
use Civi\Funding\Permission\Util\FlattenedPermissionsUtil;
use Civi\RemoteTools\Api4\Api4;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Authorization\PossiblePermissionsLoaderInterface;
use Civi\RemoteTools\RequestContext\RequestContextInterface;
use Webmozart\Assert\Assert;

final class GetAction extends DAOGetAction {

  use IsFieldSelectedTrait;

  private static ?string $drawdownAcceptionDateOperator = NULL;

  private static ?string $drawdownAcceptionDateValue = NULL;

  private static ?string $drawdownCreationDateOperator = NULL;

  private static ?string $drawdownCreationDateValue = NULL;

  private bool $cachePermissionsOnly = FALSE;

  private ?Api4Interface $api4;

  private ?CiviEventDispatcherInterface $eventDispatcher;

  private ?FundingCasePermissionsCacheManager $permissionsCacheManager;

  private ?PossiblePermissionsLoaderInterface $possiblePermissionsLoader;

  private ?RequestContextInterface $requestContext;

  private ?TransferContractRouter $transferContractRouter;

  private bool $runCalled = FALSE;

  public static function getDrawdownAcceptionDateOperator(): ?string {
    return self::$drawdownAcceptionDateOperator;
  }

  public static function getDrawdownAcceptionDateValue(): ?string {
    return self::$drawdownAcceptionDateValue;
  }

  public static function getDrawdownCreationDateOperator(): ?string {
    return self::$drawdownCreationDateOperator;
  }

  public static function getDrawdownCreationDateValue(): ?string {
    return self::$drawdownCreationDateValue;
  }

  public function __construct(
    ?Api4Interface $api4 = NULL,
    ?CiviEventDispatcherInterface $eventDispatcher = NULL,
    ?FundingCasePermissionsCacheManager $permissionsCacheManager = NULL,
    ?PossiblePermissionsLoaderInterface $possiblePermissionsLoader = NULL,
    ?RequestContextInterface $requestContext = NULL,
    ?TransferContractRouter $transferContractRouterRecreate = NULL
  ) {
    parent::__construct(FundingCase::getEntityName(), 'get');
    $this->api4 = $api4;
    $this->eventDispatcher = $eventDispatcher;
    $this->permissionsCacheManager = $permissionsCacheManager;
    $this->possiblePermissionsLoader = $possiblePermissionsLoader;
    $this->requestContext = $requestContext;
    $this->transferContractRouter = $transferContractRouterRecreate;
  }

  // phpcs:disable Generic.Metrics.CyclomaticComplexity.TooHigh
  public function _run(Result $result): void {
  // phpcs:enable
    $this->runCalled = TRUE;

    $rowCountSelected = $this->isRowCountSelected();
    if ($rowCountSelected || $this->cachePermissionsOnly) {
      $this->ensurePermissions();
    }

    if ($this->cachePermissionsOnly) {
      return;
    }

    if ($this->isRowCountSelectedOnly()) {
      parent::_run($result);

      return;
    }

    if ([] === $this->getSelect()) {
      $this->addSelect('*');
    }
    $this->addSelect('_pc.id');
    $this->addSelect('_pc.permissions');

    if (!$this->isFieldSelected('id')) {
      $this->addSelect('id');
    }

    if ($this->isFieldExplicitlySelected('withdrawable_funds')) {
      // amount_drawdowns_accepted needs to be selected before because it is referenced.
      array_unshift($this->select, 'amount_drawdowns_accepted');
    }

    $possiblePermissions = $this->getPossiblePermissions();
    /**
     * @phpstan-var array<int, list<string>> Mapping of funding case ID to permissions.
     * Used in case we have the same funding case ID multiple times because of a join.
     */
    $casePermissions = [];

    $limit = $this->getLimit();
    $offset = $this->getOffset();
    $records = [];
    do {
      parent::_run($result);

      /** @phpstan-var array<string, mixed>&array{id: int, '_pc.id': int|null, '_pc.permissions': list<string>|null} $record */
      foreach ($result as $record) {
        $record['permissions'] = $record['_pc.permissions'];
        if (NULL === $record['permissions']) {
          $record['permissions'] = ($casePermissions[$record['id']] ??= $this->determineAndCachePermissions($record));
          if ([] === $record['permissions']) {
            continue;
          }
        }

        unset($record['_pc.permissions']);
        unset($record['_pc.id']);

        FlattenedPermissionsUtil::addFlattenedPermissions($record, $record['permissions'], $possiblePermissions);
        $record['transfer_contract_uri'] =
          isset($record['id']) ? $this->getTransferContractRouter()->generate($record['id']) : NULL;

        $clearingProcessFields = array_intersect([
          'amount_cleared',
          'amount_admitted',
        ], $this->getSelect());
        if ([] !== $clearingProcessFields) {
          $clearingProcessAmounts = $this->getApi4()->execute(FundingClearingProcess::getEntityName(), 'get', [
            'select' => array_map(fn (string $field) => 'SUM(' . $field . ') AS SUM_' . $field, $clearingProcessFields),
            'where' => [
              ['application_process_id.funding_case_id', '=', $record['id']],
            ],
            'groupBy' => ['application_process_id.funding_case_id'],
          ])->first();
        }

        foreach ($clearingProcessFields as $field) {
          $record[$field] = $clearingProcessAmounts['SUM_' . $field] ?? NULL;
        }

        $records[] = $record;
      }

      $limitBefore = $this->getLimit();
      $this->setOffset($offset + count($records));
      $this->setLimit($limit - count($records));
    } while ($this->getLimit() > 0 && count($result) === $limitBefore);

    $result->exchangeArray($records);
    if (!$rowCountSelected) {
      $result->rowCount = count($records);
    }

    self::$drawdownAcceptionDateOperator = NULL;
    self::$drawdownAcceptionDateValue = NULL;
    self::$drawdownCreationDateValue = NULL;
    self::$drawdownCreationDateOperator = NULL;
  }

  public function isCachePermissionsOnly(): bool {
    return $this->cachePermissionsOnly;
  }

  /**
   * @param bool $cachePermissionsOnly
   *   If TRUE it is ensured that for all queried funding cases the permissions
   *   are cached without returning any data.
   */
  public function setCachePermissionsOnly(bool $cachePermissionsOnly): self {
    $this->cachePermissionsOnly = $cachePermissionsOnly;

    return $this;
  }

  // phpcs:disable Generic.Metrics.CyclomaticComplexity.TooHigh
  public function setDefaultWhereClause(): void {
  // phpcs:enable
    if (!$this->runCalled) {
      // _run() was not called, e.g. aggregation line in SearchKit.
      // See \Civi\Api4\Action\SearchDisplay\Run::processResult()
      $this->ensurePermissions();
    }

    // See sql_renderer in GetFieldsAction. Using static variables to transfer
    // the values is a bit hacky. The approach with non-static public methods
    // and access via \Civi\Api4\Query\Api4SelectQuery::getApiParam() does only
    // work if FundingCase is not used as joined entity.
    $maxDepth = 'AND' === ($this->where[0] ?? NULL) && is_array($this->where[1] ?? NULL) ? 1 : 0;
    $drawdownAcceptionDateClause = WhereUtil::getAndUnsetClause($this->where, 'drawdown_acception_date', $maxDepth);
    if (NULL === $drawdownAcceptionDateClause) {
      self::$drawdownAcceptionDateOperator = NULL;
      self::$drawdownAcceptionDateValue = NULL;
    }
    else {
      Assert::string($drawdownAcceptionDateClause[1]);
      Assert::nullOrString($drawdownAcceptionDateClause[2] ?? NULL);
      self::$drawdownAcceptionDateOperator = $drawdownAcceptionDateClause[1];
      self::$drawdownAcceptionDateValue = $drawdownAcceptionDateClause[2] ?? NULL;
    }

    $drawdownCreationDateClause = WhereUtil::getAndUnsetClause($this->where, 'drawdown_creation_date', $maxDepth);
    if (NULL === $drawdownCreationDateClause) {
      self::$drawdownCreationDateOperator = NULL;
      self::$drawdownCreationDateValue = NULL;
    }
    else {
      Assert::string($drawdownCreationDateClause[1]);
      Assert::nullOrString($drawdownCreationDateClause[2] ?? NULL);
      self::$drawdownCreationDateOperator = $drawdownCreationDateClause[1];
      self::$drawdownCreationDateValue = $drawdownCreationDateClause[2] ?? NULL;
    }

    FundingCasePermissionsUtil::addPermissionsCacheJoin(
      $this,
      'id',
      $this->getRequestContext()->getContactId(),
      $this->getRequestContext()->isRemote()
    );
    FundingCasePermissionsUtil::addPermissionsRestriction($this);

    parent::setDefaultWhereClause();
  }

  private function getApi4(): Api4Interface {
    return $this->api4 ??= Api4::getInstance();
  }

  private function getEventDispatcher(): CiviEventDispatcherInterface {
    return $this->eventDispatcher ??= \Civi::dispatcher();
  }

  private function getPermissionsCacheManager(): FundingCasePermissionsCacheManager {
    // @phpstan-ignore return.type, assign.propertyType
    return $this->permissionsCacheManager ??= \Civi::service(FundingCasePermissionsCacheManager::class);
  }

  public function getPossiblePermissionsLoader(): PossiblePermissionsLoaderInterface {
    // @phpstan-ignore return.type, assign.propertyType
    return $this->possiblePermissionsLoader ??= \Civi::service(PossiblePermissionsLoaderInterface::class);
  }

  private function getRequestContext(): RequestContextInterface {
    // @phpstan-ignore return.type, assign.propertyType
    return $this->requestContext ??= \Civi::service(RequestContextInterface::class);
  }

  public function getTransferContractRouter(): TransferContractRouter {
    // @phpstan-ignore return.type, assign.propertyType
    return $this->transferContractRouter ??= \Civi::service(TransferContractRouter::class);
  }

  /**
   * @phpstan-param array{id: int, '_pc.id': int|null} $record
   *
   * @phpstan-return list<string>
   */
  private function determineAndCachePermissions(array $record): array {
    $permissionsGetEvent = new GetPermissionsEvent($record['id'], $this->getRequestContext()->getContactId());
    $this->getEventDispatcher()->dispatch(GetPermissionsEvent::class, $permissionsGetEvent);

    $permissions = $permissionsGetEvent->getPermissions();

    if (NULL !== $record['_pc.id']) {
      $this->getPermissionsCacheManager()->update($record['_pc.id'], $permissions);
    }
    else {
      $this->getPermissionsCacheManager()->add(
        $record['id'],
        $this->getRequestContext()->getContactId(),
        $this->getRequestContext()->isRemote(),
        $permissions
      );
    }

    return $permissions;
  }

  /**
   * Ensures that for all relevant (i.e. not excluded via join or where) funding
   * cases the permissions have been determined.
   *
   * @throws \CRM_Core_Exception
   */
  private function ensurePermissions(): void {
    $daoGetAction = new DAOGetAction($this->getEntityName(), 'get');
    $daoGetAction
      ->setCheckPermissions($this->getCheckPermissions())
      ->setSelect(['id', '_pc.id'])
      ->setWhere($this->getWhere())
      ->addWhere('_pc.permissions', 'IS NULL')
      ->setJoin($this->getJoin())
      ->setGroupBy(['id']);

    FundingCasePermissionsUtil::addPermissionsCacheJoin(
      $daoGetAction,
      'id',
      $this->getRequestContext()->getContactId(),
      $this->getRequestContext()->isRemote()
    );

    $result = new Result();
    $daoGetAction->_run($result);
    /** @phpstan-var array{id: int, '_pc.id': int|null} $record */
    foreach ($result as $record) {
      $this->determineAndCachePermissions($record);
    }
  }

  /**
   * @phpstan-return list<string>
   */
  private function getPossiblePermissions(): array {
    return array_keys($this->getPossiblePermissionsLoader()->getFilteredPermissions($this->getEntityName()));
  }

}
