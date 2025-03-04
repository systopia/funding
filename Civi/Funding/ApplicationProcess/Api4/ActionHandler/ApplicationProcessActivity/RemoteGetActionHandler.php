<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\Funding\ApplicationProcess\Api4\ActionHandler\ApplicationProcessActivity;

use Civi\Api4\FundingApplicationProcessActivity;
use Civi\Api4\Generic\Result;
use Civi\Funding\ActivityTypeIds;
use Civi\Funding\ActivityTypeNames;
use Civi\Funding\Api4\Action\Remote\RemoteFundingGetAction;
use Civi\Funding\Api4\ActionHandler\AbstractRemoteFundingGetActionHandler;

final class RemoteGetActionHandler extends AbstractRemoteFundingGetActionHandler {

  public const ENTITY_NAME = 'RemoteFundingApplicationProcessActivity';

  protected function getEntityName(): string {
    return FundingApplicationProcessActivity::getEntityName();
  }

  public function get(RemoteFundingGetAction $action): Result {
    $action->addClause('OR',
      ['activity_type_id', '=', ActivityTypeIds::FUNDING_APPLICATION_CREATE],
      ['activity_type_id', '=', ActivityTypeIds::FUNDING_APPLICATION_STATUS_CHANGE],
      ['activity_type_id', '=', ActivityTypeIds::FUNDING_APPLICATION_COMMENT_EXTERNAL],
      ['activity_type_id:name', '=', ActivityTypeNames::FUNDING_CLEARING_CREATE],
      ['activity_type_id:name', '=', ActivityTypeNames::FUNDING_CLEARING_STATUS_CHANGE],
    );

    return parent::get($action);
  }

  protected function getParams(RemoteFundingGetAction $action): array {
    /** @var \Civi\Funding\Api4\Action\Remote\ApplicationProcessActivity\GetAction $action */
    return ['applicationProcessId' => $action->getApplicationProcessId()]
      + parent::getParams($action);
  }

}
