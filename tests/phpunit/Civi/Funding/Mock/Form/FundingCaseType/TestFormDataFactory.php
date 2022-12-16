<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

namespace Civi\Funding\Mock\Form\FundingCaseType;

use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Form\ApplicationFormDataFactoryInterface;

final class TestFormDataFactory implements ApplicationFormDataFactoryInterface {

  public static function getSupportedFundingCaseTypes(): array {
    return ['TestCaseType'];
  }

  /**
   * @inheritDoc
   */
  public function createFormData(ApplicationProcessEntity $applicationProcess, FundingCaseEntity $fundingCase): array {
    return [
      'title' => $applicationProcess->getTitle(),
      'shortDescription' => $applicationProcess->getShortDescription(),
      'recipient' => $fundingCase->getRecipientContactId(),
      'startDate' => $applicationProcess->getStartDate(),
      'endDate' => $applicationProcess->getEndDate(),
      'amountRequested' => $applicationProcess->getAmountRequested(),
    ];
  }

}
