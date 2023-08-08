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

namespace Civi\Funding\Form\SonstigeAktivitaet;

use Civi\Funding\Entity\ApplicationProcessEntity;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Form\ApplicationFormDataFactoryInterface;
use Civi\Funding\SonstigeAktivitaet\AVK1FinanzierungFactory;
use Civi\Funding\SonstigeAktivitaet\AVK1KostenFactory;
use Civi\Funding\SonstigeAktivitaet\AVK1ProjektunterlagenFactory;
use Civi\Funding\SonstigeAktivitaet\Traits\AVK1SupportedFundingCaseTypesTrait;

final class AVK1FormDataFactory implements ApplicationFormDataFactoryInterface {

  use AVK1SupportedFundingCaseTypesTrait;

  private AVK1FinanzierungFactory $avk1FinanzierungFactory;

  private AVK1KostenFactory $avk1KostenFactory;

  private AVK1ProjektunterlagenFactory $avk1ProjektunterlagenFactory;

  public function __construct(
    AVK1FinanzierungFactory $avk1FinanzierungFactory,
    AVK1KostenFactory $avk1KostenFactory,
    AVK1ProjektunterlagenFactory $avk1ProjektunterlagenFactory
  ) {
    $this->avk1FinanzierungFactory = $avk1FinanzierungFactory;
    $this->avk1KostenFactory = $avk1KostenFactory;
    $this->avk1ProjektunterlagenFactory = $avk1ProjektunterlagenFactory;
  }

  /**
   * @inheritDoc
   */
  public function createFormData(ApplicationProcessEntity $applicationProcess, FundingCaseEntity $fundingCase): array {
    $data = [];
    $data['titel'] = $applicationProcess->getTitle();
    $data['kurzbeschreibungDesInhalts'] = $applicationProcess->getShortDescription();
    $data['teilnehmer'] = $applicationProcess->getRequestData()['teilnehmer'];
    $data['empfaenger'] = $fundingCase->getRecipientContactId();
    $data['zeitraeume'] = $applicationProcess->getRequestData()['zeitraeume'];
    $data['kosten'] = $this->avk1KostenFactory->createKosten($applicationProcess);
    $data['finanzierung'] = $this->avk1FinanzierungFactory->createFinanzierung($applicationProcess);
    $data['beschreibung'] = $applicationProcess->getRequestData()['beschreibung'];
    $data['projektunterlagen'] = $this->avk1ProjektunterlagenFactory->createProjektunterlagen($applicationProcess);

    return $data;
  }

}
