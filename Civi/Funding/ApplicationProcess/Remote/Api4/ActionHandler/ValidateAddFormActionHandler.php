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

use Civi\Funding\Api4\Action\Remote\ApplicationProcess\ValidateAddFormAction;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormAddValidateCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormAddValidateHandlerInterface;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Webmozart\Assert\Assert;

final class ValidateAddFormActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'RemoteFundingApplicationProcess';

  private ApplicationFormAddValidateHandlerInterface $validateHandler;

  private FundingCaseManager $fundingCaseManager;

  public function __construct(
    ApplicationFormAddValidateHandlerInterface $validateHandler,
    FundingCaseManager $fundingCaseManager,
  ) {
    $this->validateHandler = $validateHandler;
    $this->fundingCaseManager = $fundingCaseManager;
  }

  /**
   * @phpstan-return array{
   *   valid: bool,
   *   errors: array<string, non-empty-list<string>>,
   * }
   *
   * @throws \CRM_Core_Exception
   */
  public function validateAddForm(ValidateAddFormAction $action): array {
    $fundingCaseBundle = $this->fundingCaseManager->getBundle($action->getFundingCaseId());
    Assert::notNull($fundingCaseBundle, sprintf('Funding case with id "%d" not found', $action->getFundingCaseId()));

    $validateResult = $this->validateHandler->handle(new ApplicationFormAddValidateCommand(
      $action->getResolvedContactId(),
      $fundingCaseBundle,
      $action->getData(),
    ));

    return [
      'valid' => $validateResult->isValid(),
      'errors' => $validateResult->getErrorMessages(),
    ];
  }

}
