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

use Civi\Funding\Api4\Action\FundingClearingProcess\ValidateFormAction;
use Civi\Funding\ClearingProcess\ClearingProcessBundleLoader;
use Civi\Funding\ClearingProcess\Command\ClearingFormValidateCommand;
use Civi\Funding\ClearingProcess\Handler\ClearingFormValidateHandlerInterface;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Webmozart\Assert\Assert;

/**
 * @phpstan-type validateResultT array{
 *   valid: bool,
 *   data: array<string, mixed>,
 *   errors: non-empty-array<string, non-empty-list<string>>|\stdClass,
 * }
 * 'data' contains the data after validation. 'errors' contains JSON pointers
 * mapped to error messages.
 */
final class ValidateFormActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'FundingClearingProcess';

  private ClearingProcessBundleLoader $clearingProcessBundleLoader;

  private ClearingFormValidateHandlerInterface $validateHandler;

  public function __construct(
    ClearingProcessBundleLoader $clearingProcessBundleLoader,
    ClearingFormValidateHandlerInterface $validateHandler
  ) {
    $this->clearingProcessBundleLoader = $clearingProcessBundleLoader;
    $this->validateHandler = $validateHandler;
  }

  /**
   * @phpstan-return validateResultT
   *
   * @throws \CRM_Core_Exception
   */
  public function validateForm(ValidateFormAction $action): array {
    $clearingProcessBundle = $this->clearingProcessBundleLoader->get($action->getId());
    Assert::notNull($clearingProcessBundle, sprintf('Clearing process with ID %d not found', $action->getId()));

    $result = $this->validateHandler->handle(
      new ClearingFormValidateCommand($clearingProcessBundle, $action->getData(), 10)
    );
    $data = array_map(fn ($value) => [] === $value ? new \stdClass() : $value, $result->getData());

    return [
      'valid' => $result->isValid(),
      'data' => $data,
      'errors' => [] === $result->getErrorMessages() ? new \stdClass() : $result->getErrorMessages(),
    ];
  }

}
