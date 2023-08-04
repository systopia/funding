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
use Civi\Funding\ApplicationProcess\Command\ApplicationFormSubmitCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormDataGetHandlerInterface;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormSubmitHandlerInterface;
use Civi\RemoteTools\RequestContext\RequestContextInterface;
use Webmozart\Assert\Assert;

/**
 * @method $this setData(array $data)
 * @method $this setId(int $id)
 */
final class SubmitFormAction extends AbstractAction {

  /**
   * @var array
   * @phpstan-var array<string, mixed>
   * @required
   */
  protected ?array $data = NULL;

  /**
   * @var int
   * @required
   */
  protected ?int $id = NULL;

  private ApplicationProcessBundleLoader $applicationProcessBundleLoader;

  private ApplicationFormDataGetHandlerInterface $formDataGetHandler;

  private ApplicationFormSubmitHandlerInterface $submitFormHandler;

  private RequestContextInterface $requestContext;

  public function __construct(
    ApplicationProcessBundleLoader $applicationProcessBundleLoader,
    ApplicationFormDataGetHandlerInterface $formDataGetHandler,
    ApplicationFormSubmitHandlerInterface $submitFormHandler,
    RequestContextInterface $requestContext
  ) {
    parent::__construct(FundingApplicationProcess::_getEntityName(), 'submitForm');
    $this->applicationProcessBundleLoader = $applicationProcessBundleLoader;
    $this->formDataGetHandler = $formDataGetHandler;
    $this->submitFormHandler = $submitFormHandler;
    $this->requestContext = $requestContext;
  }

  /**
   * @inheritDoc
   *
   * @throws \CRM_Core_Exception
   */
  public function _run(Result $result): void {
    $command = $this->createCommand();
    $commandResult = $this->submitFormHandler->handle($command);

    if ([] === $commandResult->getValidationResult()->getErrorMessages()) {
      $result['data'] = $this->formDataGetHandler->handle(
        new ApplicationFormDataGetCommand($command->getApplicationProcessBundle())
      );
      $result['errors'] = new \stdClass();
    }
    else {
      $result['data'] = $commandResult->getValidatedData()->getRawData();
      $result['errors'] = $commandResult->getValidationResult()->getErrorMessages();
    }
  }

  /**
   * @throws \CRM_Core_Exception
   */
  protected function createCommand(): ApplicationFormSubmitCommand {
    Assert::notNull($this->id);
    Assert::notNull($this->data);
    $applicationProcessBundle = $this->applicationProcessBundleLoader->get($this->id);
    Assert::notNull($applicationProcessBundle);

    return new ApplicationFormSubmitCommand(
      $this->requestContext->getContactId(),
      $applicationProcessBundle,
      $this->data
    );
  }

}
