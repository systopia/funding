<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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
use Civi\Api4\Generic\DAOGetAction;
use Civi\Api4\Generic\Result;
use Civi\Funding\Api4\Action\Traits\IsFieldSelectedTrait;
use Civi\Funding\Api4\Util\FundingCasePermissionsUtil;
use Civi\Funding\Api4\Util\WhereUtil;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Util\SelectUtil;
use Civi\RemoteTools\RequestContext\RequestContextInterface;

/**
 * This action can be used for entities that reference funding case and thus the
 * result shall be limited to those records for which the user has permission to
 * access the referenced funding case.
 */
abstract class AbstractReferencingDAOGetAction extends DAOGetAction {

  use IsFieldSelectedTrait;

  protected Api4Interface $_api4;

  protected FundingCaseManager $_fundingCaseManager;

  protected RequestContextInterface $_requestContext;

  protected string $_fundingCaseIdFieldName = 'funding_case_id';

  /**
   * @phpstan-var array<string, bool>
   */
  private array $fieldSelected = [];

  /**
   * @phpstan-var array<string>
   */
  private ?array $originalSelect = NULL;

  public function __construct(
    string $entityName,
    Api4Interface $api4,
    FundingCaseManager $fundingCaseManager,
    RequestContextInterface $requestContext
  ) {
    parent::__construct($entityName, 'get');
    $this->_api4 = $api4;
    $this->_fundingCaseManager = $fundingCaseManager;
    $this->_requestContext = $requestContext;
  }

  public function _run(Result $result): void {
    $this->initOriginalSelect();

    $rowCountSelected = $this->isRowCountSelected();
    if ($rowCountSelected) {
      $this->ensureFundingCasePermissions();
    }

    FundingCasePermissionsUtil::addPermissionsCacheJoin(
      $this,
      $this->_fundingCaseIdFieldName,
      $this->_requestContext->getContactId(),
      $this->_requestContext->isRemote()
    );
    FundingCasePermissionsUtil::addPermissionsRestriction($this);

    if (!$this->isFieldSelected($this->_fundingCaseIdFieldName)) {
      if ([] === $this->getSelect()) {
        $this->setSelect(['*']);
      }
      $this->addSelect($this->_fundingCaseIdFieldName);
    }

    $limit = $this->getLimit();
    $offset = $this->getOffset();
    $records = [];
    do {
      parent::_run($result);

      /** @phpstan-var array<string, mixed>&array{funding_case_id: int} $record */
      foreach ($result as $record) {
        if ($this->handleRecord($record)) {
          $this->unsetIfNotSelected($record, $this->_fundingCaseIdFieldName);
          $records[] = $record;
        }
      }

      $limitBefore = $this->getLimit();
      $this->setOffset($offset + count($records));
      $this->setLimit($limit - count($records));
    } while ($this->getLimit() > 0 && count($result) === $limitBefore);

    $result->exchangeArray($records);
    if (!$rowCountSelected) {
      $result->rowCount = count($records);
    }
  }

  /**
   * Ensures that at least the funding cases which are relevant have permissions
   * cached.
   *
   * @throws \CRM_Core_Exception
   */
  protected function ensureFundingCasePermissions(): void {
    $action = FundingCase::get(FALSE)
      ->setCachePermissionsOnly(TRUE);

    $fundingCaseId = WhereUtil::getInt($this->getWhere(), $this->_fundingCaseIdFieldName);
    if (NULL !== $fundingCaseId) {
      $action->addWhere('id', '=', $fundingCaseId);
    }

    $this->_api4->executeAction($action);
  }

  /**
   * @phpstan-param array<string, mixed> $record
   *
   * @return bool TRUE if the record shall be added to the result.
   *
   * @throws \CRM_Core_Exception
   */
  protected function handleRecord(array &$record): bool {
    // @phpstan-ignore-next-line
    return $this->_fundingCaseManager->hasAccess($record[$this->_fundingCaseIdFieldName]);
  }

  protected function initOriginalSelect(): void {
    $this->originalSelect ??= $this->getSelect();
  }

  /**
   * @phpstan-return array<string>
   */
  protected function getOriginalSelect(): array {
    return $this->originalSelect ?? $this->getSelect();
  }

  /**
   * For DAO entities isFieldExplicitlySelected() has to be used for fields of
   * type "Extra". Those fields are not part of the result if "*" is selected.
   *
   * @see isFieldExplicitlySelected()
   */
  protected function isFieldSelected(string $fieldName): bool {
    if (NULL === $this->originalSelect) {
      return SelectUtil::isFieldSelected($fieldName, $this->getSelect());
    }

    return $this->fieldSelected[$fieldName] ??= SelectUtil::isFieldSelected($fieldName, $this->getOriginalSelect());
  }

  protected function isFieldExplicitlySelected(string $fieldName): bool {
    return in_array($fieldName, $this->getOriginalSelect(), TRUE);
  }

  protected function isRowCountSelected(): bool {
    return $this->isFieldExplicitlySelected('row_count');
  }

  /**
   * @phpstan-param array<string, mixed> $record
   */
  protected function unsetIfNotSelected(array &$record, string $fieldName): void {
    if (!$this->isFieldSelected($fieldName)) {
      unset($record[$fieldName]);
    }
  }

}
