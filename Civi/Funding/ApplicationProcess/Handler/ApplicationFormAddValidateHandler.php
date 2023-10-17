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

use Civi\Funding\ApplicationProcess\Command\ApplicationFormAddValidateCommand;
use Civi\Funding\Form\Application\ApplicationValidationResult;
use Civi\Funding\Form\Application\CombinedApplicationValidatorInterface;

final class ApplicationFormAddValidateHandler implements ApplicationFormAddValidateHandlerInterface {

  private CombinedApplicationValidatorInterface $validator;

  public function __construct(CombinedApplicationValidatorInterface $validator) {
    $this->validator = $validator;
  }

  public function handle(ApplicationFormAddValidateCommand $command): ApplicationValidationResult {
    return $this->validator->validateAdd(
      $command->getFundingProgram(),
      $command->getFundingCaseType(),
      $command->getFundingCase(),
      $command->getData(),
      20
    );
  }

}
