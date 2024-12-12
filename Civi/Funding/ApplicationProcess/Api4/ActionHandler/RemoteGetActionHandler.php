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

namespace Civi\Funding\ApplicationProcess\Api4\ActionHandler;

use Civi\Api4\FundingApplicationProcess;
use Civi\Funding\Api4\Action\Remote\RemoteFundingGetAction;
use Civi\Funding\Api4\ActionHandler\AbstractRemoteFundingGetActionHandler;
use Civi\Funding\Api4\Util\WhereUtil;

final class RemoteGetActionHandler extends AbstractRemoteFundingGetActionHandler {

  public const ENTITY_NAME = 'RemoteFundingApplicationProcess';

  protected function getEntityName(): string {
    return FundingApplicationProcess::getEntityName();
  }

  protected function getJoin(RemoteFundingGetAction $action): array {
    if (in_array('funding_clearing_process.status', $action->getSelect(), TRUE)
      || WhereUtil::containsField($action->getWhere(), 'funding_clearing_process.status')
    ) {
      return [
        [
          'FundingClearingProcess AS funding_clearing_process',
          'LEFT',
          ['funding_clearing_process.application_process_id', '=', 'id'],
        ],
      ];
    }

    return [];
  }

}
