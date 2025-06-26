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

namespace Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Clearing;

use Civi\Api4\Contact;
use Civi\Funding\ClearingProcess\Form\ReportDataLoaderInterface;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Traits\HiHSupportedFundingCaseTypesTrait;
use Civi\RemoteTools\Api4\Api4Interface;

final class HiHReportDataLoader implements ReportDataLoaderInterface {

  use HiHSupportedFundingCaseTypesTrait;

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @inheritDoc
   */
  public function getReportData(ClearingProcessEntityBundle $clearingProcessBundle): array {
    $applicationProcess = $clearingProcessBundle->getApplicationProcess();
    $fundingCase = $clearingProcessBundle->getFundingCase();

    $recipientContact = $this->api4->execute(Contact::getEntityName(), 'get', [
      'select' => ['display_name', 'legal_name'],
      'where' => [['id', '=', $fundingCase->getRecipientContactId()]],
    ])->single();
    $empfaenger = $recipientContact['legal_name'] ?? $recipientContact['display_name'];

    $projekttraeger = $this->api4->execute(Contact::getEntityName(), 'get', [
      'select' => ['display_name'],
      'where' => [['id', '=', $fundingCase->getCreationContactId()]],
    ])->single()['display_name'];

    return [
      'titel' => $applicationProcess->getTitle(),
      'antragsnummer' => $applicationProcess->getIdentifier(),
      'laufzeitVon' => $applicationProcess->getStartDate()?->format('Y-m-d'),
      'laufzeitBis' => $applicationProcess->getEndDate()?->format('Y-m-d'),
      'empfaenger' => $empfaenger,
      'projekttraeger' => $projekttraeger,
    ] + $clearingProcessBundle->getClearingProcess()->getReportData();
  }

}
