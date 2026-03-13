<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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
use Civi\Funding\FundingCaseType\AbstractFundingCaseTypeServiceCollector;

/**
 * @extends AbstractFundingCaseTypeServiceCollector<ReceiptsFormGeneratorInterface>
 */
// phpcs:ignore Generic.Files.LineLength.TooLong
final class ReceiptsFormGeneratorCollector extends AbstractFundingCaseTypeServiceCollector implements ReceiptsFormGeneratorInterface {

  public function generateReceiptsForm(ClearingProcessEntityBundle $clearingProcessBundle): JsonFormsFormInterface {
    return $this
      ->getService($clearingProcessBundle->getFundingCaseType()->getName())
      ->generateReceiptsForm($clearingProcessBundle);
  }

}
