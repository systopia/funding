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

namespace Civi\Funding\FundingCase\Handler;

use Civi\Funding\Form\FundingCase\FundingCaseValidationResult;
use Civi\Funding\Form\FundingCase\FundingCaseValidatorInterface;
use Civi\Funding\FundingCase\Command\FundingCaseFormUpdateValidateCommand;

final class FundingCaseFormUpdateValidateHandler implements FundingCaseFormUpdateValidateHandlerInterface {

  private FundingCaseValidatorInterface $validator;

  public function __construct(FundingCaseValidatorInterface $validator) {
    $this->validator = $validator;
  }

  public function handle(FundingCaseFormUpdateValidateCommand $command): FundingCaseValidationResult {
    return $this->validator->validateUpdate(
      $command->getFundingProgram(),
      $command->getFundingCaseType(),
      $command->getFundingCase(),
      $command->getData(),
      $command->getMaxErrors(),
    );
  }

}
