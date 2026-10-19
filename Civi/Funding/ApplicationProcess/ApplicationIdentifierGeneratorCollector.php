<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\ApplicationProcess;

use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\FundingCaseType\AbstractFundingCaseTypeServiceCollector;

/**
 * @extends AbstractFundingCaseTypeServiceCollector<ApplicationIdentifierGeneratorInterface>
 */
// phpcs:ignore Generic.Files.LineLength.TooLong
final class ApplicationIdentifierGeneratorCollector extends AbstractFundingCaseTypeServiceCollector implements ApplicationIdentifierGeneratorInterface {

  public function generateIdentifier(ApplicationProcessEntityBundle $applicationProcessBundle): string {
    return $this->getGenerator($applicationProcessBundle)->generateIdentifier($applicationProcessBundle);
  }

  public function generateIdentifierOnFundingCaseChange(ApplicationProcessEntityBundle $applicationProcessBundle
  ): string {
    return $this->getGenerator($applicationProcessBundle)->generateIdentifierOnFundingCaseChange(
      $applicationProcessBundle);
  }

  public function getGenerator(
    ApplicationProcessEntityBundle $applicationProcessBundle
  ): ApplicationIdentifierGeneratorInterface {
    return $this->getService($applicationProcessBundle->getFundingCaseType()->getName());
  }

}
