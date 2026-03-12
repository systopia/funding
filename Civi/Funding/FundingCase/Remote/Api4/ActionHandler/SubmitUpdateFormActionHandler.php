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

use Civi\Funding\Api4\Action\Remote\FundingCase\SubmitUpdateFormAction;
use Civi\Funding\Form\RemoteSubmitResponseActions;
use Civi\Funding\FundingCase\Actions\FundingCaseActions;
use Civi\Funding\FundingCase\Command\FundingCaseFormUpdateSubmitCommand;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\Handler\FundingCaseFormUpdateSubmitHandlerInterface;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use CRM_Funding_ExtensionUtil as E;
use Webmozart\Assert\Assert;

final class SubmitUpdateFormActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'RemoteFundingCase';

  private FundingCaseManager $fundingCaseManager;

  private FundingCaseFormUpdateSubmitHandlerInterface $submitHandler;

  public function __construct(
    FundingCaseManager $fundingCaseManager,
    FundingCaseFormUpdateSubmitHandlerInterface $submitHandler
  ) {
    $this->fundingCaseManager = $fundingCaseManager;
    $this->submitHandler = $submitHandler;
  }

  /**
   * @phpstan-return array{
   *    action: RemoteSubmitResponseActions::*,
   *    message: string,
   *    errors?: array<string, non-empty-list<string>>,
   *  }
   *
   * @throws \CRM_Core_Exception
   */
  public function submitUpdateForm(SubmitUpdateFormAction $action): array {
    $fundingCaseBundle = $this->fundingCaseManager->getBundle($action->getFundingCaseId());
    Assert::notNull($fundingCaseBundle, sprintf('Funding case with ID %d not found', $action->getFundingCaseId()));

    $submitResult = $this->submitHandler->handle(new FundingCaseFormUpdateSubmitCommand(
      $action->getResolvedContactId(),
      $fundingCaseBundle,
      $action->getData(),
    ));

    if (!$submitResult->isSuccess()) {
      return [
        'action' => RemoteSubmitResponseActions::SHOW_VALIDATION,
        'message' => E::ts('Validation failed'),
        'errors' => $submitResult->getValidationResult()->getErrorMessages(),
      ];
    }

    if (FundingCaseActions::DELETE === $submitResult->getValidatedData()->getAction()) {
      return [
        'action' => RemoteSubmitResponseActions::CLOSE_FORM,
        'message' => E::ts('Deleted'),
      ];
    }

    return [
      'action' => RemoteSubmitResponseActions::RELOAD_FORM,
      'message' => E::ts('Saved'),
    ];
  }

}
