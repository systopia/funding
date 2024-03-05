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

namespace Civi\Funding\ClearingProcess\Api4\ActionHandler;

use Civi\Api4\FundingClearingProcess;
use Civi\Funding\Api4\Action\Remote\FundingClearingProcess\GetFormAction;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Civi\RemoteTools\Api4\Api4Interface;

/**
 * @phpstan-import-type formResultT from GetFormActionHandler
 */
final class RemoteGetFormActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'RemoteFundingClearingProcess';

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @phpstan-return formResultT
   *
   * @throws \CRM_Core_Exception
   */
  public function getForm(GetFormAction $action): array {
    // @phpstan-ignore-next-line
    return $this->api4->execute(FundingClearingProcess::getEntityName(), 'getForm', [
      'id' => $action->getId(),
    ])->getArrayCopy();
  }

}
