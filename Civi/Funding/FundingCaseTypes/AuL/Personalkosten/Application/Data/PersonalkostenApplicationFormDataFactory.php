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

namespace Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Application\Data;

use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Form\Application\ApplicationFormDataFactoryInterface;
use Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Traits\PersonalkostenSupportedFundingCaseTypesTrait;

final class PersonalkostenApplicationFormDataFactory implements ApplicationFormDataFactoryInterface {

  use PersonalkostenSupportedFundingCaseTypesTrait;

  private PersonalkostenDokumenteFactory $dokumenteFactory;

  public function __construct(PersonalkostenDokumenteFactory $dokumenteFactory) {
    $this->dokumenteFactory = $dokumenteFactory;
  }

  /**
   * @inheritDoc
   *
   * @throws \CRM_Core_Exception
   */
  public function createFormData(ApplicationProcessEntity $applicationProcess, FundingCaseEntity $fundingCase): array {
    $data = $applicationProcess->getRequestData();
    $data['empfaenger'] = $fundingCase->getRecipientContactId();
    $data['dokumente'] = $this->dokumenteFactory->createDokumente($applicationProcess);

    return $data;
  }

  /**
   * @inheritDoc
   *
   * @throws \CRM_Core_Exception
   */
  public function createFormDataForCopy(
    ApplicationProcessEntity $applicationProcess,
    FundingCaseEntity $fundingCase
  ): array {
    return $this->createFormData($applicationProcess, $fundingCase);
  }

}
