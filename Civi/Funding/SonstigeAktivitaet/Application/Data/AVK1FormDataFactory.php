<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\Funding\SonstigeAktivitaet\Application\Data;

use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Form\Application\ApplicationFormDataFactoryInterface;
use Civi\Funding\SonstigeAktivitaet\Traits\AVK1SupportedFundingCaseTypesTrait;

final class AVK1FormDataFactory implements ApplicationFormDataFactoryInterface {

  use AVK1SupportedFundingCaseTypesTrait;

  private AVK1FinanzierungFactory $avk1FinanzierungFactory;

  private AVK1ProjektunterlagenFactory $avk1ProjektunterlagenFactory;

  public function __construct(
    AVK1FinanzierungFactory $avk1FinanzierungFactory,
    AVK1ProjektunterlagenFactory $avk1ProjektunterlagenFactory
  ) {
    $this->avk1FinanzierungFactory = $avk1FinanzierungFactory;
    $this->avk1ProjektunterlagenFactory = $avk1ProjektunterlagenFactory;
  }

  /**
   * @inheritDoc
   *
   * @throws \CRM_Core_Exception
   */
  public function createFormData(ApplicationProcessEntity $applicationProcess, FundingCaseEntity $fundingCase): array {
    $data = $applicationProcess->getRequestData();
    // @phpstan-ignore-next-line
    $data['grunddaten']['titel'] = $applicationProcess->getTitle();
    // @phpstan-ignore-next-line
    $data['grunddaten']['kurzbeschreibungDesInhalts'] = $applicationProcess->getShortDescription();
    $data['empfaenger'] = $fundingCase->getRecipientContactId();
    $data['finanzierung'] = $this->avk1FinanzierungFactory->createFinanzierung($applicationProcess);
    $data['projektunterlagen'] = $this->avk1ProjektunterlagenFactory->createProjektunterlagen($applicationProcess);

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
