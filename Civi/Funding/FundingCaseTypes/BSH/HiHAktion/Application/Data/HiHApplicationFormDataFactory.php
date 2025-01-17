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

namespace Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Application\Data;

use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Form\Application\ApplicationFormDataFactoryInterface;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Traits\HiHSupportedFundingCaseTypesTrait;
use Civi\RemoteTools\RequestContext\RequestContextInterface;

final class HiHApplicationFormDataFactory implements ApplicationFormDataFactoryInterface {

  use HiHSupportedFundingCaseTypesTrait;

  private HiHInfoDateienFactory $informationenZumProjektFactory;

  private RequestContextInterface $requestContext;

  public function __construct(
    HiHInfoDateienFactory $projektunterlagenFactory,
    RequestContextInterface $requestContext
  ) {
    $this->informationenZumProjektFactory = $projektunterlagenFactory;
    $this->requestContext = $requestContext;
  }

  /**
   * @inheritDoc
   *
   * @throws \CRM_Core_Exception
   */
  public function createFormData(ApplicationProcessEntity $applicationProcess, FundingCaseEntity $fundingCase): array {
    $data = $applicationProcess->getRequestData();
    $data['empfaenger'] = $fundingCase->getRecipientContactId();
    $infoDateien = $this->informationenZumProjektFactory->createInfoDateien($applicationProcess);
    // Initially files where not persisted in CiviCRM. In that case we do not
    // overwrite the value in $data for internal requests so it is at lease
    // visible that there have been files.
    if ([] !== $infoDateien || $this->requestContext->isRemote()) {
      // @phpstan-ignore offsetAccess.nonOffsetAccessible
      $data['informationenZumProjekt']['dateien'] = $infoDateien;
    }

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
