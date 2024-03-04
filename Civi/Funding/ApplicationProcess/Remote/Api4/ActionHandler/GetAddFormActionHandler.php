<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\Funding\ApplicationProcess\Remote\Api4\ActionHandler;

use Civi\Funding\Api4\Action\Remote\ApplicationProcess\GetAddFormAction;
use Civi\Funding\ApplicationProcess\ApplicationProcessBundleLoader;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormAddCreateCommand;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormDataGetCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormAddCreateHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormDataGetHandlerInterface;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingProgram\FundingCaseTypeManager;
use Civi\Funding\FundingProgram\FundingProgramManager;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Webmozart\Assert\Assert;

final class GetAddFormActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'RemoteFundingApplicationProcess';

  private ApplicationProcessBundleLoader $applicationProcessBundleLoader;

  private ApplicationFormAddCreateHandlerInterface $createHandler;

  private ApplicationFormDataGetHandlerInterface $formDataGetHandler;

  private FundingCaseManager $fundingCaseManager;

  private FundingCaseTypeManager $fundingCaseTypeManager;

  private FundingProgramManager $fundingProgramManager;

  public function __construct(
    ApplicationProcessBundleLoader $applicationProcessBundleLoader,
    ApplicationFormAddCreateHandlerInterface $createHandler,
    ApplicationFormDataGetHandlerInterface $formDataGetHandler,
    FundingCaseManager $fundingCaseManager,
    FundingCaseTypeManager $fundingCaseTypeManager,
    FundingProgramManager $fundingProgramManager
  ) {
    $this->applicationProcessBundleLoader = $applicationProcessBundleLoader;
    $this->createHandler = $createHandler;
    $this->formDataGetHandler = $formDataGetHandler;
    $this->fundingCaseManager = $fundingCaseManager;
    $this->fundingCaseTypeManager = $fundingCaseTypeManager;
    $this->fundingProgramManager = $fundingProgramManager;
  }

  /**
   * @phpstan-return array{
   *   jsonSchema: array<int|string, mixed>,
   *   uiSchema: array<int|string, mixed>,
   *   data: array<string, mixed>,
   * }
   *
   * @throws \CRM_Core_Exception
   */
  public function getAddForm(GetAddFormAction $action): array {
    $fundingCase = $this->fundingCaseManager->get($action->getFundingCaseId());
    Assert::notNull($fundingCase, sprintf('Funding case with id "%d" not found', $action->getFundingCaseId()));

    $fundingProgram = $this->fundingProgramManager->get($fundingCase->getFundingProgramId());
    Assert::notNull($fundingProgram);
    $fundingCaseType = $this->fundingCaseTypeManager->get($fundingCase->getFundingCaseTypeId());
    Assert::notNull($fundingCaseType);

    $form = $this->createHandler->handle(new ApplicationFormAddCreateCommand(
      $action->getResolvedContactId(),
      $fundingProgram,
      $fundingCaseType,
      $fundingCase,
    ));

    if (NULL !== $action->getCopyDataFromId()) {
      $applicationProcessBundle = $this->applicationProcessBundleLoader->get($action->getCopyDataFromId());
      Assert::notNull(
        $applicationProcessBundle,
        sprintf('Application process with ID %d not found', $action->getCopyDataFromId())
      );
      Assert::same(
        $fundingCaseType->getId(),
        $applicationProcessBundle->getFundingCaseType()->getId(),
        'Copies are only allowed with the same funding case type'
      );
      $formData = $this->formDataGetHandler->handle(new ApplicationFormDataGetCommand(
        $applicationProcessBundle,
        $this->applicationProcessBundleLoader->getStatusList($applicationProcessBundle),
        ApplicationFormDataGetCommand::FLAG_COPY
      )) + $form->getData();
    }

    return [
      'jsonSchema' => $form->getJsonSchema()->toArray(),
      'uiSchema' => $form->getUiSchema()->toArray(),
      'data' => $formData ?? $form->getData(),
    ];
  }

}
