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

namespace Civi\Api4;

use Civi\Funding\Api4\AbstractRemoteFundingEntity;
use Civi\Funding\Api4\Action\RemoteFundingCaseGetNewApplicationFormAction;
use Civi\Funding\Api4\Action\RemoteFundingCaseSubmitNewApplicationFormAction;
use Civi\Funding\Api4\Action\RemoteFundingCaseValidateNewApplicationFormAction;
use Civi\Funding\Api4\Action\RemoteFundingDAOGetAction;

final class RemoteFundingCase extends AbstractRemoteFundingEntity {

  public static function get(): RemoteFundingDAOGetAction {
    return new RemoteFundingDAOGetAction(static::getEntityName());
  }

  public static function getNewApplicationForm(): RemoteFundingCaseGetNewApplicationFormAction {
    return new RemoteFundingCaseGetNewApplicationFormAction();
  }

  public static function submitNewApplicationForm(): RemoteFundingCaseSubmitNewApplicationFormAction {
    return new RemoteFundingCaseSubmitNewApplicationFormAction();
  }

  public static function validateNewApplicationForm(): RemoteFundingCaseValidateNewApplicationFormAction {
    return new RemoteFundingCaseValidateNewApplicationFormAction();
  }

}
