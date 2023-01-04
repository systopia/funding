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
use Civi\Funding\Api4\Action\Traits\FundingActionContactIdSessionTrait;
use Civi\Funding\ApplicationProcess\ApplicationProcessBundleLoader;
use Civi\Funding\ApplicationProcess\Command\ApplicationFormValidateCommand;
use Civi\Funding\ApplicationProcess\Handler\ApplicationFormValidateHandlerInterface;
use Webmozart\Assert\Assert;

/**
 * @method $this setData(array $data)
 * @method $this setId(int $id)
 */
final class ValidateFormAction extends AbstractAction {

  use FundingActionContactIdSessionTrait;

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

  private ApplicationFormValidateHandlerInterface $validateFormHandler;

  public function __construct(
    ApplicationProcessBundleLoader $applicationProcessBundleLoader,
    ApplicationFormValidateHandlerInterface $validateFormHandler
  ) {
    parent::__construct(FundingApplicationProcess::_getEntityName(), 'validateForm');
    $this->applicationProcessBundleLoader = $applicationProcessBundleLoader;
    $this->validateFormHandler = $validateFormHandler;
  }

  /**
   * @inheritDoc
   *
   * @throws \API_Exception
   */
  public function _run(Result $result): void {
    $command = $this->createCommand();
    $commandResult = $this->validateFormHandler->handle($command);

    $result['valid'] = $commandResult->isValid();
    $result['data'] = $commandResult->getData();
    $result['errors'] = [] === $commandResult->getErrors()
      ? new \stdClass() : $commandResult->getErrors();
  }

  /**
   * @throws \API_Exception
   */
  protected function createCommand(): ApplicationFormValidateCommand {
    Assert::notNull($this->id);
    Assert::notNull($this->data);
    $applicationProcessBundle = $this->applicationProcessBundleLoader->get($this->id);
    Assert::notNull($applicationProcessBundle);

    return new ApplicationFormValidateCommand($applicationProcessBundle, $this->data);
  }

}
