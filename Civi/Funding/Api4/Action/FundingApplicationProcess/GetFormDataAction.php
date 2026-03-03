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
use Civi\Funding\Api4\Action\Traits\ApplicationProcessManagerTrait;
use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormDataGetCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormDataGetHandlerInterface;
use Civi\RemoteTools\Api4\Action\Traits\IdParameterTrait;
use Webmozart\Assert\Assert;

final class GetFormDataAction extends AbstractAction {

  use IdParameterTrait;

  use ApplicationProcessManagerTrait;

  private ?ApplicationFormDataGetHandlerInterface $formDataGetHandler;

  public function __construct(
    ?ApplicationProcessManager $applicationProcessManager = NULL,
    ?ApplicationFormDataGetHandlerInterface $formDataGetHandler = NULL
  ) {
    parent::__construct(FundingApplicationProcess::getEntityName(), 'getFormData');
    $this->_applicationProcessManager = $applicationProcessManager;
    $this->formDataGetHandler = $formDataGetHandler;
  }

  /**
   * @inheritDoc
   *
   * @throws \CRM_Core_Exception
   */
  public function _run(Result $result): void {
    $result['data'] = $this->getFormDataGetHandler()->handle($this->createCommand());
  }

  /**
   * @throws \CRM_Core_Exception
   */
  protected function createCommand(): ApplicationFormDataGetCommand {
    $applicationProcessBundle = $this->getApplicationProcessManager()->getBundle($this->getId());
    Assert::notNull($applicationProcessBundle);
    $statusList = $this->getApplicationProcessManager()->getStatusList($applicationProcessBundle);

    return new ApplicationFormDataGetCommand($applicationProcessBundle, $statusList);
  }

  private function getFormDataGetHandler(): ApplicationFormDataGetHandlerInterface {
    // @phpstan-ignore return.type, assign.propertyType
    return $this->formDataGetHandler ??= \Civi::service(ApplicationFormDataGetHandlerInterface::class);
  }

}
