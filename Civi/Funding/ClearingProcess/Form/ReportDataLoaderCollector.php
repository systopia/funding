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
use Psr\Container\ContainerInterface;

final class ReportDataLoaderCollector implements ReportDataLoaderInterface {

  private ContainerInterface $dataLoaders;

  /**
   * @param \Psr\Container\ContainerInterface $dataLoaders
   *   Data loaders with funding case type name as ID.
   */
  public function __construct(ContainerInterface $dataLoaders) {
    $this->dataLoaders = $dataLoaders;
  }

  public function getReportData(ClearingProcessEntityBundle $clearingProcessBundle): array {
    $fundingCaseTypeName = $clearingProcessBundle->getFundingCaseType()->getName();
    if ($this->dataLoaders->has($fundingCaseTypeName)) {
      /** @var \Civi\Funding\ClearingProcess\Form\ReportDataLoaderInterface $dataLoader */
      $dataLoader = $this->dataLoaders->get($fundingCaseTypeName);

      return $dataLoader->getReportData($clearingProcessBundle);
    }

    return $clearingProcessBundle->getClearingProcess()->getReportData();
  }

}
