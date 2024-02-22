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

use Civi\Funding\ClearingProcess\ClearingFormsGenerator;
use Civi\Funding\ClearingProcess\Command\ClearingJsonFormsFormGetCommand;
use Civi\Funding\Form\JsonFormsFormInterface;

final class ClearingJsonFormsFormGetHandler implements ClearingJsonFormsFormGetHandlerInterface {

  private ClearingFormsGenerator $clearingFormsGenerator;

  public function __construct(ClearingFormsGenerator $clearingFormsGenerator) {
    $this->clearingFormsGenerator = $clearingFormsGenerator;
  }

  public function handle(ClearingJsonFormsFormGetCommand $command): JsonFormsFormInterface {
    return $this->clearingFormsGenerator->generateForm($command->getClearingProcessBundle());
  }

}
