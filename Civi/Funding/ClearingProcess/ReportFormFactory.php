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

namespace Civi\Funding\ClearingProcess;

use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\Form\JsonFormsForm;
use Civi\Funding\Form\JsonFormsFormInterface;
use Psr\Container\ContainerInterface;

final class ReportFormFactory implements ReportFormFactoryInterface {

  private ContainerInterface $formFactories;

  /**
   * @param \Psr\Container\ContainerInterface $formFactories
   *   Form factories with funding case type name as ID.
   */
  public function __construct(ContainerInterface $formFactories) {
    $this->formFactories = $formFactories;
  }

  public function createReportForm(ClearingProcessEntityBundle $clearingProcessBundle): JsonFormsFormInterface {
    $fundingCaseType = $clearingProcessBundle->getFundingCaseType();
    if ($this->formFactories->has($fundingCaseType->getName())) {
      /** @var \Civi\Funding\ClearingProcess\ReportFormFactoryInterface $formFactory */
      $formFactory = $this->formFactories->get($fundingCaseType->getName());

      return $formFactory->createReportForm($clearingProcessBundle);
    }

    return JsonFormsForm::newEmpty();
  }

}
