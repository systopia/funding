<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
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

declare(strict_types=1);

namespace Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Clearing;

use Civi\Funding\ClearingProcess\Form\ReportDataLoaderInterface;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Traits\PersonalkostenSupportedFundingCaseTypesTrait;

final class PersonalkostenReportDataLoader implements ReportDataLoaderInterface {

  use PersonalkostenSupportedFundingCaseTypesTrait;

  /**
   * @inheritDoc
   */
  public function getReportData(ClearingProcessEntityBundle $clearingProcessBundle): array {
    $requestData = $clearingProcessBundle->getApplicationProcess()->getRequestData();

    return [
        'internerBezeichner' => $requestData['internerBezeichner'] ?? '',
        'name' => $requestData['name'] ?? '',
        'vorname' => $requestData['vorname'] ?? '',
        'tarifUndEingruppierung' => $requestData['tarifUndEingruppierung'] ?? '',
        'beginn' => $requestData['beginn'] ?? '',
        'ende' => $requestData['ende'] ?? '',
        'personalkostenTatsaechlich' => $requestData['personalkostenTatsaechlich'] ?? '',
        'personalkostenBeantragt' => $requestData['personalkostenBeantragt'] ?? '',
        'sachkostenpauschale' => $requestData['sachkostenpauschale'] ?? '',
        'beantragterZuschuss' => $requestData['beantragterZuschuss'] ?? '',
        'empfaenger' => $requestData['empfaenger'] ?? '',
        'dokumente' => $requestData['dokumente'] ?? [],
        'titel' => $requestData['titel'] ?? '',
        'kurzbeschreibung' => $requestData['kurzbeschreibung'] ?? '',
      ] + $clearingProcessBundle->getClearingProcess()->getReportData();
  }

}
