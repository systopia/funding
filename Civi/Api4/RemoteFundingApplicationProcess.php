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
use Civi\Funding\Api4\Action\Remote\ApplicationProcess\GetAddFormAction;
use Civi\Funding\Api4\Action\Remote\ApplicationProcess\GetFormAction;
use Civi\Funding\Api4\Action\Remote\ApplicationProcess\GetTemplateRenderUriAction;
use Civi\Funding\Api4\Action\Remote\ApplicationProcess\GetTemplatesAction;
use Civi\Funding\Api4\Action\Remote\ApplicationProcess\SubmitAddFormAction;
use Civi\Funding\Api4\Action\Remote\ApplicationProcess\SubmitFormAction;
use Civi\Funding\Api4\Action\Remote\ApplicationProcess\ValidateAddFormAction;
use Civi\Funding\Api4\Action\Remote\ApplicationProcess\ValidateFormAction;
use Civi\Funding\Api4\Action\Remote\RemoteFundingGetAction;

/**
 * The "Add" form actions are used to add an application to an existing funding
 * case. The form actions without "add" are used for existing applications.
 */
final class RemoteFundingApplicationProcess extends AbstractRemoteFundingEntity {

  public static function get(): RemoteFundingGetAction {
    return new RemoteFundingGetAction(self::getEntityName(), __FUNCTION__);
  }

  public static function getTemplateRenderUri(): GetTemplateRenderUriAction {
    return new GetTemplateRenderUriAction();
  }

  public static function getTemplates(): GetTemplatesAction {
    return new GetTemplatesAction();
  }

  public static function getAddForm(): GetAddFormAction {
    return new GetAddFormAction();
  }

  public static function validateAddForm(): ValidateAddFormAction {
    return new ValidateAddFormAction();
  }

  public static function submitAddForm(): SubmitAddFormAction {
    return new SubmitAddFormAction();
  }

  public static function getForm(): GetFormAction {
    return new GetFormAction();
  }

  public static function submitForm(): SubmitFormAction {
    return new SubmitFormAction();
  }

  public static function validateForm(): ValidateFormAction {
    return new ValidateFormAction();
  }

}
