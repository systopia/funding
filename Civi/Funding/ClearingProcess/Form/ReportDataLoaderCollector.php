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
use Civi\Funding\FundingCaseType\AbstractFundingCaseTypeServiceCollector;

/**
 * @extends AbstractFundingCaseTypeServiceCollector<ReportDataLoaderInterface>
 */
// phpcs:ignore Generic.Files.LineLength.TooLong
final class ReportDataLoaderCollector extends AbstractFundingCaseTypeServiceCollector implements ReportDataLoaderInterface {

  public function getReportData(ClearingProcessEntityBundle $clearingProcessBundle): array {
    $fundingCaseTypeName = $clearingProcessBundle->getFundingCaseType()->getName();
    if ($this->hasService($fundingCaseTypeName)) {
      return $this->getService($fundingCaseTypeName)->getReportData($clearingProcessBundle);
    }

    return $clearingProcessBundle->getClearingProcess()->getReportData();
  }

}
