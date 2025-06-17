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

namespace Civi\Funding\ClearingProcess\Form;

use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\Form\JsonFormsFormInterface;
use Psr\Container\ContainerInterface;

final class ReceiptsFormFactoryCollector implements ReceiptsFormGeneratorInterface {

  private ContainerInterface $formFactories;

  /**
   * @param \Psr\Container\ContainerInterface $formFactories
   *   Form factories with funding case type name as ID.
   */
  public function __construct(ContainerInterface $formFactories) {
    $this->formFactories = $formFactories;
  }

  public function generateReceiptsForm(ClearingProcessEntityBundle $clearingProcessBundle): JsonFormsFormInterface {
    return $this->getFactory(
      $clearingProcessBundle->getFundingCaseType()->getName()
    )->generateReceiptsForm($clearingProcessBundle);
  }

  private function getFactory(string $fundingCaseTypeName): ReceiptsFormGeneratorInterface {
    // @phpstan-ignore return.type
    return $this->formFactories->has($fundingCaseTypeName)
      ? $this->formFactories->get($fundingCaseTypeName) : $this->formFactories->get('*');
  }

}
