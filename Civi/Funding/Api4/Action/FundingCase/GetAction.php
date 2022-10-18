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
use Civi\Core\CiviEventDispatcher;
use Civi\Funding\Api4\Action\FundingContactIdSessionAwareInterface;
use Civi\Funding\Api4\Action\Traits\FundingActionContactIdSessionTrait;
use Civi\Funding\Event\FundingCase\GetPermissionsEvent;
use Civi\RemoteTools\Api4\Action\Traits\PermissionsGetActionTrait;
use Civi\RemoteTools\Authorization\PossiblePermissionsLoaderInterface;

final class GetAction extends DAOGetAction implements FundingContactIdSessionAwareInterface {

  use FundingActionContactIdSessionTrait;
  use PermissionsGetActionTrait;

  private CiviEventDispatcher $_eventDispatcher;

  private PossiblePermissionsLoaderInterface $_possiblePermissionsLoader;

  public function __construct(CiviEventDispatcher $eventDispatcher,
    PossiblePermissionsLoaderInterface $possiblePermissionsLoader
  ) {
    parent::__construct(FundingCase::_getEntityName(), 'get');
    $this->_eventDispatcher = $eventDispatcher;
    $this->_possiblePermissionsLoader = $possiblePermissionsLoader;
  }

  /**
   * @param array{id: int} $record
   *
   * @return array<string>
   */
  protected function getRecordPermissions(array $record): array {
    $permissionsGetEvent = new GetPermissionsEvent($record['id'], $this->getContactId());
    $this->_eventDispatcher->dispatch(GetPermissionsEvent::class, $permissionsGetEvent);

    return $permissionsGetEvent->getPermissions();
  }

  /**
   * @phpstan-return array<string>
   */
  protected function getPossiblePermissions(): array {
    return $this->_possiblePermissionsLoader->getPermissions($this->getEntityName());
  }

}
