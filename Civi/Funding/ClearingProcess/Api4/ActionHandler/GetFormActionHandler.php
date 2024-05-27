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

namespace Civi\Funding\ClearingProcess\Api4\ActionHandler;

use Civi\Funding\Api4\Action\FundingClearingProcess\GetFormAction;
use Civi\Funding\ClearingProcess\ClearingProcessBundleLoader;
use Civi\Funding\ClearingProcess\Command\ClearingFormDataGetCommand;
use Civi\Funding\ClearingProcess\Command\ClearingFormGetCommand;
use Civi\Funding\ClearingProcess\Handler\ClearingFormDataGetHandlerInterface;
use Civi\Funding\ClearingProcess\Handler\ClearingFormGetHandlerInterface;
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

  public const ENTITY_NAME = 'FundingClearingProcess';

  private ClearingProcessBundleLoader $clearingProcessBundleLoader;

  private ClearingFormDataGetHandlerInterface $formDataGetHandler;

  private ClearingFormGetHandlerInterface $formGetHandler;

  public function __construct(
    ClearingProcessBundleLoader $clearingProcessBundleLoader,
    ClearingFormDataGetHandlerInterface $formDataGetHandler,
    ClearingFormGetHandlerInterface $formGetHandler
  ) {
    $this->clearingProcessBundleLoader = $clearingProcessBundleLoader;
    $this->formDataGetHandler = $formDataGetHandler;
    $this->formGetHandler = $formGetHandler;
  }

  /**
   * @phpstan-return formResultT
   *
   * @throws \CRM_Core_Exception
   */
  public function getForm(GetFormAction $action): array {
    $clearingProcessBundle = $this->clearingProcessBundleLoader->get($action->getId());
    Assert::notNull($clearingProcessBundle, sprintf('Clearing process with ID %d not found', $action->getId()));

    $form = $this->formGetHandler->handle(new ClearingFormGetCommand($clearingProcessBundle));
    $data = $this->formDataGetHandler->handle(new ClearingFormDataGetCommand($clearingProcessBundle));
    $data = array_map(fn ($value) => [] === $value ? new \stdClass() : $value, $data);

    return [
      'jsonSchema' => $form->getJsonSchema()->toArray(),
      'uiSchema' => $form->getUiSchema()->toArray(),
      'data' => $data,
    ];
  }

}
