<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\ApplicationProcess\Api4\ActionHandler;

use Civi\Api4\FundingApplicationProcess;
use Civi\Funding\Api4\Action\Remote\ApplicationProcess\SubmitFormAction;
use Civi\Funding\Api4\OptionsLoaderInterface;
use Civi\Funding\ApplicationProcess\ApplicationProcessBundleLoader;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormSubmitCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormSubmitHandlerInterface;
use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\ExternalFileEntity;
use Civi\Funding\Form\RemoteSubmitResponseActions;
use Civi\RemoteTools\ActionHandler\ActionHandlerInterface;
use CRM_Funding_ExtensionUtil as E;
use Webmozart\Assert\Assert;

final class RemoteSubmitFormActionHandler implements ActionHandlerInterface {

  public const ENTITY_NAME = 'RemoteFundingApplicationProcess';

  private ApplicationProcessBundleLoader $applicationProcessBundleLoader;

  private OptionsLoaderInterface $optionsLoader;

  private ApplicationFormSubmitHandlerInterface $submitHandler;

  public function __construct(
    ApplicationProcessBundleLoader $applicationProcessBundleLoader,
    OptionsLoaderInterface $optionsLoader,
    ApplicationFormSubmitHandlerInterface $submitHandler
  ) {
    $this->applicationProcessBundleLoader = $applicationProcessBundleLoader;
    $this->optionsLoader = $optionsLoader;
    $this->submitHandler = $submitHandler;
  }

  /**
   * @return array{
   *   action: string,
   *   message: string,
   *   files?: array<string, string>,
   *   errors?: array<string, non-empty-list<string>>,
   *   }
   *
   * @throws \CRM_Core_Exception
   */
  public function submitForm(SubmitFormAction $action): array {
    $applicationProcessBundle = $this->applicationProcessBundleLoader->get($action->getApplicationProcessId());
    Assert::notNull(
      $applicationProcessBundle,
      E::ts('Application process with ID "%1" not found', [1 => $action->getApplicationProcessId()])
    );

    $statusList = $this->applicationProcessBundleLoader->getStatusList($applicationProcessBundle);

    $command = new ApplicationFormSubmitCommand(
      $action->getResolvedContactId(),
      $applicationProcessBundle,
      $statusList,
      $action->getData(),
    );

    $result = $this->submitHandler->handle($command);
    if ($result->isSuccess()) {
      return [
        'action' => $result->getValidationResult()->isReadOnly()
        && 'delete' !== $result->getValidatedData()->getAction()
        ? RemoteSubmitResponseActions::RELOAD_FORM
        : RemoteSubmitResponseActions::CLOSE_FORM,
        'message' => $this->createSuccessMessage($applicationProcessBundle->getApplicationProcess()),
        'files' => $this->convertFiles($result->getFiles()),
      ];
    }

    return [
      'action' => RemoteSubmitResponseActions::SHOW_VALIDATION,
      'message' => E::ts('Validation failed'),
      'errors' => $result->getValidationResult()->getErrorMessages(),
    ];
  }

  /**
   * @param array<string, \Civi\Funding\Entity\ExternalFileEntity> $files
   *
   * @return array<string, string>
   */
  private function convertFiles(array $files): array {
    return array_map(
      fn (ExternalFileEntity $file) => $file->getUri(),
      $files,
    );
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function createSuccessMessage(ApplicationProcessEntity $applicationProcess): string {
    return E::ts('Saved (Status: %1)', [
      1 => $this->optionsLoader->getOptionLabel(
        FundingApplicationProcess::getEntityName(),
        'status',
        $applicationProcess->getStatus()
      ),
    ]);
  }

}
