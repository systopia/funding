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

namespace Civi\Funding\Api4\Action\FundingClearingResourcesItem;

use Civi\Api4\FundingClearingResourcesItem;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\RequestContext\RequestContextInterface;

final class GetAction extends \Civi\Funding\Api4\Action\Generic\ClearingItem\GetAction {

  public function __construct(
    ?Api4Interface $api4 = NULL,
    ?FundingCaseManager $fundingCaseManager = NULL,
    ?RequestContextInterface $requestContext = NULL
  ) {
    parent::__construct(
      FundingClearingResourcesItem::getEntityName(),
      $api4,
      $fundingCaseManager,
      $requestContext
    );
  }

}
