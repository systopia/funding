<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

namespace Civi\Funding\ApplicationProcess\Handler;

use Civi\Funding\ApplicationProcess\ApplicationExternalFileManagerInterface;
use Civi\Funding\ApplicationProcess\ApplicationFormFilesFactoryInterface;
use Civi\Funding\ApplicationProcess\Command\ApplicationFilesPersistCommand;

final class ApplicationFilesPersistHandler implements ApplicationFilesPersistHandlerInterface {

  private ApplicationExternalFileManagerInterface $externalFileManager;

  private ApplicationFormFilesFactoryInterface $formFilesFactory;

  public function __construct(
    ApplicationExternalFileManagerInterface $externalFileManager,
    ApplicationFormFilesFactoryInterface $formFilesFactory
  ) {
    $this->externalFileManager = $externalFileManager;
    $this->formFilesFactory = $formFilesFactory;
  }

  /**
   * @inheritDoc
   *
   * @throws \CRM_Core_Exception
   */
  public function handle(ApplicationFilesPersistCommand $command): array {
    $externalFiles = [];
    $usedIdentifiers = [];
    foreach ($this->formFilesFactory->createFormFiles($command->getRequestData()) as $file) {
      $externalFiles[$file->getUri()] = $this->externalFileManager->addOrUpdateFile(
        $file->getUri(),
        $file->getIdentifier(),
        $command->getApplicationProcess()->getId(),
        $file->getCustomData(),
      );
      $usedIdentifiers[] = $file->getIdentifier();
    }

    $this->externalFileManager->deleteFiles($command->getApplicationProcess()->getId(), $usedIdentifiers);

    return $externalFiles;
  }

}
