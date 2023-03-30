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
use Civi\Api4\Generic\DAOGetAction;
use Civi\Api4\Generic\Result;
use Civi\Core\CiviEventDispatcherInterface;
use Civi\Funding\Event\FundingCase\GetPermissionsEvent;
use Civi\Funding\FundingCase\TransferContractRouter;
use Civi\Funding\Session\FundingSessionInterface;
use Civi\RemoteTools\Api4\Action\Traits\PermissionsGetActionTrait;
use Civi\RemoteTools\Authorization\PossiblePermissionsLoaderInterface;

final class GetAction extends DAOGetAction {

  use PermissionsGetActionTrait {
    PermissionsGetActionTrait::_run as permissionsGetRun;
  }

  private CiviEventDispatcherInterface $_eventDispatcher;

  private PossiblePermissionsLoaderInterface $_possiblePermissionsLoader;

  private FundingSessionInterface $session;

  private TransferContractRouter $transferContractRouter;

  public function __construct(CiviEventDispatcherInterface $eventDispatcher,
    PossiblePermissionsLoaderInterface $possiblePermissionsLoader,
    FundingSessionInterface $session,
    TransferContractRouter $transferContractRouterRecreate
  ) {
    parent::__construct(FundingCase::_getEntityName(), 'get');
    $this->_eventDispatcher = $eventDispatcher;
    $this->_possiblePermissionsLoader = $possiblePermissionsLoader;
    $this->session = $session;
    $this->transferContractRouter = $transferContractRouterRecreate;
  }

  public function _run(Result $result): void {
    $this->permissionsGetRun($result);
    /** @phpstan-var array{id?: int} $record */
    foreach ($result as &$record) {
      $record['transfer_contract_uri'] = isset($record['id'])
        ? $this->transferContractRouter->generate($record['id']) : NULL;
    }
  }

  /**
   * @param array{id: int} $record
   *
   * @return array<string>
   */
  protected function getRecordPermissions(array $record): array {
    $permissionsGetEvent = new GetPermissionsEvent($record['id'], $this->session->getContactId());
    $this->_eventDispatcher->dispatch(GetPermissionsEvent::class, $permissionsGetEvent);

    return $permissionsGetEvent->getPermissions();
  }

  /**
   * @phpstan-return array<string>
   */
  protected function getPossiblePermissions(): array {
    return \array_keys($this->_possiblePermissionsLoader->getFilteredPermissions($this->getEntityName()));
  }

}
