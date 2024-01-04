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

use Civi\Funding\ApplicationProcess\Handler\ApplicationActionApplyHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationAllowedActionsGetHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationCostItemsAddIdentifiersHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationCostItemsPersistHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationDeleteHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFilesAddIdentifiersHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFilesPersistHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormAddCreateHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormAddSubmitHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormAddValidateHandlerInterface;
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
use Civi\Funding\ApplicationProcess\Handler\ApplicationSnapshotCreateHandlerInterface;
use Civi\Funding\FundingCase\Handler\FundingCaseApproveHandlerInterface;
use Civi\Funding\FundingCase\Handler\FundingCaseFormDataGetHandlerInterface;
use Civi\Funding\FundingCase\Handler\FundingCaseFormNewGetHandlerInterface;
use Civi\Funding\FundingCase\Handler\FundingCaseFormNewSubmitHandlerInterface;
use Civi\Funding\FundingCase\Handler\FundingCaseFormNewValidateHandlerInterface;
use Civi\Funding\FundingCase\Handler\FundingCaseFormUpdateGetHandlerInterface;
use Civi\Funding\FundingCase\Handler\FundingCaseFormUpdateSubmitHandlerInterface;
use Civi\Funding\FundingCase\Handler\FundingCaseFormUpdateValidateHandlerInterface;
use Civi\Funding\FundingCase\Handler\FundingCasePossibleActionsGetHandlerInterface;
use Civi\Funding\FundingCase\Handler\FundingCaseUpdateAmountApprovedHandlerInterface;
use Civi\Funding\FundingCase\Handler\TransferContractRecreateHandlerInterface;
use Civi\Funding\FundingCase\StatusDeterminer\FundingCaseStatusDeterminerInterface;
use Civi\Funding\TransferContract\Handler\TransferContractRenderHandlerInterface;

interface FundingCaseTypeServiceLocatorInterface {

  public const SERVICE_TAG = 'funding.case.type.service_locator';

  public function getApplicationActionApplyHandler(): ApplicationActionApplyHandlerInterface;

  public function getApplicationAllowedActionsGetHandler(): ApplicationAllowedActionsGetHandlerInterface;

  public function getApplicationDeleteHandler(): ApplicationDeleteHandlerInterface;

  public function getApplicationFilesAddIdentifiersHandler(): ApplicationFilesAddIdentifiersHandlerInterface;

  public function getApplicationFilesPersistHandler(): ApplicationFilesPersistHandlerInterface;

  public function getApplicationFormAddCreateHandler(): ?ApplicationFormAddCreateHandlerInterface;

  public function getApplicationFormAddSubmitHandler(): ?ApplicationFormAddSubmitHandlerInterface;

  public function getApplicationFormAddValidateHandler(): ?ApplicationFormAddValidateHandlerInterface;

  public function getApplicationFormNewCreateHandler(): ?ApplicationFormNewCreateHandlerInterface;

  public function getApplicationFormNewValidateHandler(): ?ApplicationFormNewValidateHandlerInterface;

  public function getApplicationFormNewSubmitHandler(): ?ApplicationFormNewSubmitHandlerInterface;

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

  public function getApplicationSnapshotCreateHandler(): ApplicationSnapshotCreateHandlerInterface;

  public function getFundingCaseApproveHandler(): FundingCaseApproveHandlerInterface;

  public function getFundingCaseFormDataGetHandler(): ?FundingCaseFormDataGetHandlerInterface;

  public function getFundingCaseFormNewGetHandler(): ?FundingCaseFormNewGetHandlerInterface;

  public function getFundingCaseFormNewSubmitHandler(): ?FundingCaseFormNewSubmitHandlerInterface;

  public function getFundingCaseFormNewValidateHandler(): ?FundingCaseFormNewValidateHandlerInterface;

  public function getFundingCaseFormUpdateGetHandler(): ?FundingCaseFormUpdateGetHandlerInterface;

  public function getFundingCaseFormUpdateSubmitHandler(): ?FundingCaseFormUpdateSubmitHandlerInterface;

  public function getFundingCaseFormUpdateValidateHandler(): ?FundingCaseFormUpdateValidateHandlerInterface;

  public function getFundingCasePossibleActionsGetHandler(): FundingCasePossibleActionsGetHandlerInterface;

  public function getFundingCaseStatusDeterminer(): FundingCaseStatusDeterminerInterface;

  public function getFundingCaseUpdateAmountApprovedHandler(): FundingCaseUpdateAmountApprovedHandlerInterface;

  public function getTransferContractRecreateHandler(): TransferContractRecreateHandlerInterface;

  public function getTransferContractRenderHandler(): TransferContractRenderHandlerInterface;

}
