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

use Civi\Funding\Api4\AbstractRemoteFundingEntityLegacy;
use Civi\Funding\Api4\Action\Remote\RemoteFundingDAOGetActionLegacy;
use Civi\Funding\Api4\Action\Remote\FundingCase\GetUpdateFormAction;
use Civi\Funding\Api4\Action\Remote\FundingCase\GetNewFormAction;
use Civi\Funding\Api4\Action\Remote\FundingCase\GetNewApplicationFormAction;
use Civi\Funding\Api4\Action\Remote\FundingCase\SubmitUpdateFormAction;
use Civi\Funding\Api4\Action\Remote\FundingCase\SubmitNewFormAction;
use Civi\Funding\Api4\Action\Remote\FundingCase\SubmitNewApplicationFormAction;
use Civi\Funding\Api4\Action\Remote\FundingCase\ValidateUpdateFormAction;
use Civi\Funding\Api4\Action\Remote\FundingCase\ValidateNewFormAction;
use Civi\Funding\Api4\Action\Remote\FundingCase\ValidateNewApplicationFormAction;

/**
 * The new application form actions are used to create a funding case together
 * with an application. The other new form actions are used for combined
 * applications to create a funding case without an application.
 */
final class RemoteFundingCase extends AbstractRemoteFundingEntityLegacy {

  public static function get(): RemoteFundingDAOGetActionLegacy {
    return new RemoteFundingDAOGetActionLegacy(static::getEntityName());
  }

  public static function getUpdateForm(): GetUpdateFormAction {
    return new GetUpdateFormAction();
  }

  public static function submitUpdateForm(): SubmitUpdateFormAction {
    return new SubmitUpdateFormAction();
  }

  public static function validateUpdateForm(): ValidateUpdateFormAction {
    return new ValidateUpdateFormAction();
  }

  public static function getNewForm(): GetNewFormAction {
    return new GetNewFormAction();
  }

  public static function submitNewForm(): SubmitNewFormAction {
    return new SubmitNewFormAction();
  }

  public static function validateNewForm(): ValidateNewFormAction {
    return new ValidateNewFormAction();
  }

  public static function getNewApplicationForm(): GetNewApplicationFormAction {
    return \Civi::service(GetNewApplicationFormAction::class);
  }

  public static function submitNewApplicationForm(): SubmitNewApplicationFormAction {
    return \Civi::service(SubmitNewApplicationFormAction::class);
  }

  public static function validateNewApplicationForm(): ValidateNewApplicationFormAction {
    return \Civi::service(ValidateNewApplicationFormAction::class);
  }

}
