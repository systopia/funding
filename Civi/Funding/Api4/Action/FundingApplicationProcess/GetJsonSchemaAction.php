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
use Civi\Funding\Api4\Action\Traits\ApplicationProcessBundleLoaderTrait;
use Civi\Funding\ApplicationProcess\ApplicationProcessBundleLoader;
use Civi\Funding\ApplicationProcess\Command\ApplicationJsonSchemaGetCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationJsonSchemaGetHandlerInterface;
use Civi\RemoteTools\Api4\Action\Traits\IdParameterTrait;
use CRM_Funding_ExtensionUtil as E;
use Webmozart\Assert\Assert;

final class GetJsonSchemaAction extends AbstractAction {

  use IdParameterTrait;

  use ApplicationProcessBundleLoaderTrait;

  private ?ApplicationJsonSchemaGetHandlerInterface $jsonSchemaGetHandler;

  public function __construct(
    ?ApplicationProcessBundleLoader $applicationProcessBundleLoader = NULL,
    ?ApplicationJsonSchemaGetHandlerInterface $jsonSchemaGetHandler = NULL
  ) {
    parent::__construct(FundingApplicationProcess::getEntityName(), 'getJsonSchema');
    $this->_applicationProcessBundleLoader = $applicationProcessBundleLoader;
    $this->jsonSchemaGetHandler = $jsonSchemaGetHandler;
  }

  /**
   * @inheritDoc
   *
   * @throws \CRM_Core_Exception
   */
  public function _run(Result $result): void {
    $result['jsonSchema'] = $this->getJsonSchemaGetHandler()->handle($this->createCommand());
  }

  /**
   * @throws \CRM_Core_Exception
   */
  protected function createCommand(): ApplicationJsonSchemaGetCommand {
    $applicationProcessBundle = $this->getApplicationProcessBundleLoader()->get($this->getId());
    Assert::notNull($applicationProcessBundle, E::ts('No such application or missing permission.'));
    $statusList = $this->getApplicationProcessBundleLoader()->getStatusList($applicationProcessBundle);

    return new ApplicationJsonSchemaGetCommand($applicationProcessBundle, $statusList);
  }

  private function getJsonSchemaGetHandler(): ApplicationJsonSchemaGetHandlerInterface {
    // @phpstan-ignore return.type, assign.propertyType
    return $this->jsonSchemaGetHandler ??= \Civi::service(ApplicationJsonSchemaGetHandlerInterface::class);
  }

}
