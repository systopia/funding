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

namespace Civi\Funding;

use Civi\Funding\ApplicationProcess\Handler\ApplicationCostItemsAddIdentifiersHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationCostItemsPersistHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationDeleteHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormCreateHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormDataGetHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewCreateHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewSubmitHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormNewValidateHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormSubmitHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormValidateHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationJsonSchemaGetHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationResourcesItemsAddIdentifiersHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationResourcesItemsPersistHandlerInterface;
use Civi\Funding\FundingCase\FundingCaseStatusDeterminerInterface;

interface FundingCaseTypeServiceLocatorInterface {

  public function getApplicationDeleteHandler(): ApplicationDeleteHandlerInterface;

  public function getApplicationFormNewCreateHandler(): ApplicationFormNewCreateHandlerInterface;

  public function getApplicationFormNewValidateHandler(): ApplicationFormNewValidateHandlerInterface;

  public function getApplicationFormNewSubmitHandler(): ApplicationFormNewSubmitHandlerInterface;

  public function getApplicationFormDataGetHandler(): ApplicationFormDataGetHandlerInterface;

  public function getApplicationFormCreateHandler(): ApplicationFormCreateHandlerInterface;

  public function getApplicationFormValidateHandler(): ApplicationFormValidateHandlerInterface;

  public function getApplicationFormSubmitHandler(): ApplicationFormSubmitHandlerInterface;

  public function getApplicationJsonSchemaGetHandler(): ApplicationJsonSchemaGetHandlerInterface;

  public function getApplicationCostItemsAddIdentifiersHandler() : ApplicationCostItemsAddIdentifiersHandlerInterface;

  public function getApplicationCostItemsPersistHandler(): ApplicationCostItemsPersistHandlerInterface;

  // phpcs:disable: Generic.Files.LineLength.TooLong
  public function getApplicationResourcesItemsAddIdentifiersHandler(): ApplicationResourcesItemsAddIdentifiersHandlerInterface;

  // phpcs:enable

  public function getApplicationResourcesItemsPersistHandler(): ApplicationResourcesItemsPersistHandlerInterface;

  public function getFundingCaseStatusDeterminer(): FundingCaseStatusDeterminerInterface;

}
