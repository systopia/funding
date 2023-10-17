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

namespace Civi\Funding\Api4\Action\FundingApplicationProcess;

use Civi\Api4\FundingApplicationProcess;
use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\Generic\Result;
use Civi\Funding\ApplicationProcess\ApplicationProcessBundleLoader;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormValidateCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormValidateHandlerInterface;
use Civi\RemoteTools\Api4\Action\Traits\DataParameterTrait;
use Civi\RemoteTools\Api4\Action\Traits\IdParameterTrait;
use Webmozart\Assert\Assert;

final class ValidateFormAction extends AbstractAction {

  use DataParameterTrait;

  use IdParameterTrait;

  private ApplicationProcessBundleLoader $applicationProcessBundleLoader;

  private ApplicationFormValidateHandlerInterface $validateFormHandler;

  public function __construct(
    ApplicationProcessBundleLoader $applicationProcessBundleLoader,
    ApplicationFormValidateHandlerInterface $validateFormHandler
  ) {
    parent::__construct(FundingApplicationProcess::getEntityName(), 'validateForm');
    $this->applicationProcessBundleLoader = $applicationProcessBundleLoader;
    $this->validateFormHandler = $validateFormHandler;
  }

  /**
   * @inheritDoc
   *
   * @throws \CRM_Core_Exception
   */
  public function _run(Result $result): void {
    $command = $this->createCommand();
    $commandResult = $this->validateFormHandler->handle($command);

    $result['valid'] = $commandResult->isValid();
    $result['data'] = $commandResult->getValidatedData()->getRawData();
    $result['errors'] = [] === $commandResult->getErrorMessages()
      ? new \stdClass() : $commandResult->getErrorMessages();
  }

  /**
   * @throws \CRM_Core_Exception
   */
  protected function createCommand(): ApplicationFormValidateCommand {
    $applicationProcessBundle = $this->applicationProcessBundleLoader->get($this->getId());
    Assert::notNull($applicationProcessBundle);
    $statusList = $this->applicationProcessBundleLoader->getStatusList($applicationProcessBundle);

    return new ApplicationFormValidateCommand($applicationProcessBundle, $statusList, $this->getData(), 20);
  }

}
