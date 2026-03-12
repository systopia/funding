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

use Civi\Funding\Api4\Action\Remote\FundingCase\ValidateUpdateFormAction;
use Civi\Funding\FundingCase\Command\FundingCaseFormUpdateValidateCommand;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\Handler\FundingCaseFormUpdateValidateHandlerInterface;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Webmozart\Assert\Assert;

final class ValidateUpdateFormActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'RemoteFundingCase';

  private FundingCaseManager $fundingCaseManager;

  private FundingCaseFormUpdateValidateHandlerInterface $validateHandler;

  public function __construct(
    FundingCaseManager $fundingCaseManager,
    FundingCaseFormUpdateValidateHandlerInterface $validateHandler
  ) {
    $this->fundingCaseManager = $fundingCaseManager;
    $this->validateHandler = $validateHandler;
  }

  /**
   * @phpstan-return array{
   *   valid: bool,
   *   errors: array<string, non-empty-list<string>>,
   * }
   *
   * @throws \CRM_Core_Exception
   */
  public function validateUpdateForm(ValidateUpdateFormAction $action): array {
    $fundingCaseBundle = $this->fundingCaseManager->getBundle($action->getFundingCaseId());
    Assert::notNull($fundingCaseBundle, sprintf('Funding case with ID %d not found', $action->getFundingCaseId()));

    $validateResult = $this->validateHandler->handle(new FundingCaseFormUpdateValidateCommand(
      $fundingCaseBundle, $action->getData(), 20
    ));

    return [
      'valid' => $validateResult->isValid(),
      'errors' => $validateResult->getErrorMessages(),
    ];
  }

}
