<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

namespace Civi\Funding\ClearingProcess\Handler;

use Civi\Funding\ClearingProcess\Command\ClearingFormGetCommand;
use Civi\Funding\ClearingProcess\Command\ClearingFormValidateCommand;
use Civi\RemoteTools\JsonSchema\Validation\ValidationResult;
use Civi\RemoteTools\JsonSchema\Validation\ValidatorInterface;

final class ClearingFormValidateHandler implements ClearingFormValidateHandlerInterface {

  private ClearingFormGetHandlerInterface $formGetHandler;

  private ValidatorInterface $validator;

  public function __construct(ClearingFormGetHandlerInterface $formGetHandler, ValidatorInterface $validator) {
    $this->formGetHandler = $formGetHandler;
    $this->validator = $validator;
  }

  public function handle(ClearingFormValidateCommand $command): ValidationResult {
    $form = $this->formGetHandler->handle(new ClearingFormGetCommand($command->getClearingProcessBundle()));

    return $this->validator->validate($form->getJsonSchema(), $command->getData());
  }

}
