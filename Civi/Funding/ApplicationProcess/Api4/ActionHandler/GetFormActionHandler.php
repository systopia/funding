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

use Civi\Funding\Api4\Action\FundingApplicationProcess\GetFormAction;
use Civi\Funding\ApplicationProcess\ApplicationProcessBundleLoader;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormCreateCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormCreateHandlerInterface;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Webmozart\Assert\Assert;

/**
 * @phpstan-type formResultT array{
 *   jsonSchema: array<int|string, mixed>,
 *   uiSchema: array<int|string, mixed>,
 *   data: array<string, mixed>,
 * }
 */
final class GetFormActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'FundingApplicationProcess';

  private ApplicationProcessBundleLoader $applicationProcessBundleLoader;

  private ApplicationFormCreateHandlerInterface $createHandler;

  public function __construct(
    ApplicationProcessBundleLoader $applicationProcessBundleLoader,
    ApplicationFormCreateHandlerInterface $createHandler
  ) {
    $this->applicationProcessBundleLoader = $applicationProcessBundleLoader;
    $this->createHandler = $createHandler;
  }

  /**
   * @phpstan-return formResultT
   *
   * @throws \CRM_Core_Exception
   */
  public function getForm(GetFormAction $action): array {
    $applicationProcessBundle = $this->applicationProcessBundleLoader->get($action->getId());
    Assert::notNull($applicationProcessBundle, sprintf('Application process with ID "%d" not found', $action->getId()));
    $applicationProcessStatusList = $this->applicationProcessBundleLoader->getStatusList($applicationProcessBundle);

    $form = $this->createHandler->handle(new ApplicationFormCreateCommand(
      $applicationProcessBundle,
      $applicationProcessStatusList)
    );

    return [
      'jsonSchema' => $form->getJsonSchema()->toArray(),
      'uiSchema' => $form->getUiSchema()->toArray(),
      'data' => $form->getData(),
    ];
  }

}
