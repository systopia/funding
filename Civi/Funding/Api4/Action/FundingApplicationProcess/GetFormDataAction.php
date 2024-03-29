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
use Civi\Funding\ApplicationProcess\Command\ApplicationFormDataGetCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormDataGetHandlerInterface;
use Civi\RemoteTools\Api4\Action\Traits\IdParameterTrait;
use Webmozart\Assert\Assert;

final class GetFormDataAction extends AbstractAction {

  use IdParameterTrait;

  private ApplicationProcessBundleLoader $applicationProcessBundleLoader;

  private ApplicationFormDataGetHandlerInterface $formDataGetHandler;

  public function __construct(
    ApplicationProcessBundleLoader $applicationProcessBundleLoader,
    ApplicationFormDataGetHandlerInterface $formDataGetHandler
  ) {
    parent::__construct(FundingApplicationProcess::getEntityName(), 'getFormData');
    $this->applicationProcessBundleLoader = $applicationProcessBundleLoader;
    $this->formDataGetHandler = $formDataGetHandler;
  }

  /**
   * @inheritDoc
   *
   * @throws \CRM_Core_Exception
   */
  public function _run(Result $result): void {
    $result['data'] = $this->formDataGetHandler->handle($this->createCommand());
  }

  /**
   * @throws \CRM_Core_Exception
   */
  protected function createCommand(): ApplicationFormDataGetCommand {
    $applicationProcessBundle = $this->applicationProcessBundleLoader->get($this->getId());
    Assert::notNull($applicationProcessBundle);
    $statusList = $this->applicationProcessBundleLoader->getStatusList($applicationProcessBundle);

    return new ApplicationFormDataGetCommand($applicationProcessBundle, $statusList);
  }

}
