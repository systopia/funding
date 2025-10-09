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

use Civi\Api4\FundingClearingProcess;
use Civi\Api4\FundingProgram;
use Civi\Api4\Generic\DAOGetAction;
use Civi\Api4\Generic\Result;
use Civi\Core\CiviEventDispatcherInterface;
use Civi\Funding\Api4\Action\Traits\Api4Trait;
use Civi\Funding\Api4\Action\Traits\EventDispatcherTrait;
use Civi\Funding\Api4\Action\Traits\PossiblePermissionsLoaderTrait;
use Civi\Funding\Api4\Action\Traits\RequestContextTrait;
use Civi\Funding\Event\FundingProgram\GetPermissionsEvent;
use Civi\RemoteTools\Api4\Action\Traits\PermissionsGetActionTrait;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Authorization\PossiblePermissionsLoaderInterface;
use Civi\RemoteTools\RequestContext\RequestContextInterface;

final class GetAction extends DAOGetAction {

  // The number of funding programs is usually not large, otherwise a permission
  // caching like for funding cases would be necessary.
  use PermissionsGetActionTrait {
    PermissionsGetActionTrait::_run as traitRun;
  }

  use Api4Trait;

  use EventDispatcherTrait;

  use PossiblePermissionsLoaderTrait;

  use RequestContextTrait;

  private bool $allowEmptyRecordPermissions = FALSE;

  public function __construct(
    ?Api4Interface $api4 = NULL,
    ?CiviEventDispatcherInterface $eventDispatcher = NULL,
    ?PossiblePermissionsLoaderInterface $possiblePermissionsLoader = NULL,
    ?RequestContextInterface $requestContext = NULL
  ) {
    parent::__construct(FundingProgram::getEntityName(), 'get');
    $this->_api4 = $api4;
    $this->_eventDispatcher = $eventDispatcher;
    $this->_possiblePermissionsLoader = $possiblePermissionsLoader;
    $this->_requestContext = $requestContext;
  }

  public function _run(Result $result): void {
    $clearingProcessFields = array_intersect([
      'amount_cleared',
      'amount_admitted',
    ], $this->getSelect());
    if ([] !== $clearingProcessFields && !$this->isFieldSelected('id')) {
      $this->addSelect('id');
    }

    $this->traitRun($result);

    if ([] !== $clearingProcessFields) {
      /** @phpstan-var array<string, mixed> $record */
      foreach ($result as &$record) {
        $clearingProcessAmounts = $this->getApi4()->execute(FundingClearingProcess::getEntityName(), 'get', [
          'select' => array_map(fn (string $field) => 'SUM(' . $field . ') AS SUM_' . $field, $clearingProcessFields),
          'where' => [
            ['application_process_id.funding_case_id.funding_program_id', '=', $record['id']],
          ],
          'groupBy' => ['application_process_id.funding_case_id.funding_program_id'],
        ])->first();

        foreach ($clearingProcessFields as $field) {
          $record[$field] = $clearingProcessAmounts["SUM_$field"] ?? 0.0;
        }
      }
    }
  }

  public function isAllowEmptyRecordPermissions(): bool {
    return $this->allowEmptyRecordPermissions;
  }

  /**
   * @param bool $allowEmptyRecordPermissions
   *   If TRUE, records without permissions are not filtered from result.
   *
   * @return $this
   */
  public function setAllowEmptyRecordPermissions(bool $allowEmptyRecordPermissions): self {
    $this->allowEmptyRecordPermissions = $allowEmptyRecordPermissions;

    return $this;
  }

  /**
   * @param array{id: int} $record
   *
   * @return list<string>
   */
  protected function getRecordPermissions(array $record): array {
    $permissionsGetEvent = new GetPermissionsEvent($record['id'], $this->getRequestContext()->getContactId());
    $this->getEventDispatcher()->dispatch(GetPermissionsEvent::class, $permissionsGetEvent);

    return $permissionsGetEvent->getPermissions();
  }

  /**
   * @phpstan-return list<string>
   */
  protected function getPossiblePermissions(): array {
    return \array_keys($this->getPossiblePermissionsLoader()->getFilteredPermissions($this->getEntityName()));
  }

}
