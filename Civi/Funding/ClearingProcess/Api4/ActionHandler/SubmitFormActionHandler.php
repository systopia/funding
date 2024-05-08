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

use Civi\Funding\Api4\Action\FundingClearingProcess\SubmitFormAction;
use Civi\Funding\ClearingProcess\ClearingProcessBundleLoader;
use Civi\Funding\ClearingProcess\Command\ClearingFormSubmitCommand;
use Civi\Funding\ClearingProcess\Handler\ClearingFormSubmitHandlerInterface;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use Webmozart\Assert\Assert;

/**
 * @phpstan-type submitResultT array{
 *   data: array<string, mixed>,
 *   files: non-empty-array<string, string>|\stdClass,
 *   errors: non-empty-array<string, non-empty-list<string>>|\stdClass,
 * }
 * 'data' contains the persisted data, or the data after validation if the
 * validation failed. 'errors' contains JSON pointers mapped to error
 * messages if the validation failed.
 */
final class SubmitFormActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'FundingClearingProcess';

  private ClearingProcessBundleLoader $clearingProcessBundleLoader;

  private ClearingFormSubmitHandlerInterface $submitHandler;

  public function __construct(
    ClearingProcessBundleLoader $clearingProcessBundleLoader,
    ClearingFormSubmitHandlerInterface $submitHandler
  ) {
    $this->clearingProcessBundleLoader = $clearingProcessBundleLoader;
    $this->submitHandler = $submitHandler;
  }

  /**
   * @phpstan-return submitResultT
   *
   * @throws \CRM_Core_Exception
   */
  public function submitForm(SubmitFormAction $action): array {
    $clearingProcessBundle = $this->clearingProcessBundleLoader->get($action->getId());
    Assert::notNull($clearingProcessBundle, sprintf('Clearing process with ID %d not found', $action->getId()));

    $result = $this->submitHandler->handle(new ClearingFormSubmitCommand($clearingProcessBundle, $action->getData()));
    $data = array_map(fn ($value) => [] === $value ? new \stdClass() : $value, $result->getData());

    return [
      'data' => $data,
      'files' => [] === $result->getFiles() ? new \stdClass() : $result->getFiles(),
      'errors' => [] === $result->getErrorMessages() ? new \stdClass() : $result->getErrorMessages(),
    ];
  }

}
