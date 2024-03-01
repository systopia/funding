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
use Civi\Funding\ClearingProcess\Form\ClearingFormGenerator;
use Civi\Funding\Form\JsonFormsFormInterface;

final class ClearingFormGetHandler implements ClearingFormGetHandlerInterface {

  private ClearingFormGenerator $clearingFormsGenerator;

  /**
   * @phpstan-var array<int, JsonFormsFormInterface>
   */
  private array $forms = [];

  public function __construct(ClearingFormGenerator $clearingFormsGenerator) {
    $this->clearingFormsGenerator = $clearingFormsGenerator;
  }

  public function handle(ClearingFormGetCommand $command): JsonFormsFormInterface {
    return $this->forms[$command->getClearingProcess()->getId()] ??=
      $this->clearingFormsGenerator->generateForm($command->getClearingProcessBundle());
  }

}
