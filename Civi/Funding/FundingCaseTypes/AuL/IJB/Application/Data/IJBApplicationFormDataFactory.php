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

namespace Civi\Funding\FundingCaseTypes\AuL\IJB\Application\Data;

use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Form\Application\ApplicationFormDataFactoryInterface;
use Civi\Funding\FundingCaseTypes\AuL\IJB\Traits\IJBSupportedFundingCaseTypesTrait;

final class IJBApplicationFormDataFactory implements ApplicationFormDataFactoryInterface {

  use IJBSupportedFundingCaseTypesTrait;

  private IJBProjektunterlagenFactory $projektunterlagenFactory;

  public function __construct(IJBProjektunterlagenFactory $projektunterlagenFactory) {
    $this->projektunterlagenFactory = $projektunterlagenFactory;
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
    $data['projektunterlagen'] = $this->projektunterlagenFactory->createProjektunterlagen($applicationProcess);

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
