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

namespace Civi\Funding\FundingCase\Remote\Api4\ActionHandler;

use Civi\Funding\Api4\Action\Remote\FundingCase\GetUpdateFormAction;
use Civi\Funding\FundingCase\Command\FundingCaseFormUpdateGetCommand;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\Handler\FundingCaseFormUpdateGetHandlerInterface;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Webmozart\Assert\Assert;

final class GetUpdateFormActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'RemoteFundingCase';

  private FundingCaseFormUpdateGetHandlerInterface $formUpdateGetHandler;

  private FundingCaseManager $fundingCaseManager;

  public function __construct(
    FundingCaseFormUpdateGetHandlerInterface $formUpdateGetHandler,
    FundingCaseManager $fundingCaseManager,
  ) {
    $this->formUpdateGetHandler = $formUpdateGetHandler;
    $this->fundingCaseManager = $fundingCaseManager;
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
  public function getUpdateForm(GetUpdateFormAction $action): array {
    $fundingCaseBundle = $this->fundingCaseManager->getBundle($action->getFundingCaseId());
    Assert::notNull($fundingCaseBundle, sprintf('Funding case with ID %d not found', $action->getFundingCaseId()));

    $form = $this->formUpdateGetHandler->handle(new FundingCaseFormUpdateGetCommand(
      $action->getResolvedContactId(),
      $fundingCaseBundle,
    ));

    return [
      'jsonSchema' => $form->getJsonSchema()->toArray(),
      'uiSchema' => $form->getUiSchema()->toArray(),
      'data' => $form->getData(),
    ];
  }

}
