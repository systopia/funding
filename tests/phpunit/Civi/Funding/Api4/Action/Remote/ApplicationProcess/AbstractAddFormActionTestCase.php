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

namespace Civi\Funding\Api4\Action\Remote\ApplicationProcess;

use Civi\Funding\AbstractRemoteFundingHeadlessTestCase;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Fixtures\FundingCaseFixture;
use Civi\Funding\Fixtures\FundingCaseTypeFixture;
use Civi\Funding\Fixtures\FundingProgramContactRelationFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;

abstract class AbstractAddFormActionTestCase extends AbstractRemoteFundingHeadlessTestCase {

  protected FundingCaseTypeEntity $fundingCaseType;

  protected FundingProgramEntity $fundingProgram;

  protected FundingCaseEntity $fundingCase;

  protected string $remoteContactId;

  protected function initFixtures(): void {
    $this->fundingProgram = FundingProgramFixture::addFixture();
    $this->fundingCaseType = FundingCaseTypeFixture::addFixture();
    $recipientContact = ContactFixture::addOrganization();
    $creationContact = ContactFixture::addIndividual();
    $this->remoteContactId = (string) $creationContact['id'];

    $this->fundingCase = FundingCaseFixture::addFixture(
      $this->fundingProgram->getId(),
      $this->fundingCaseType->getId(),
      $recipientContact['id'],
      $creationContact['id'],
    );

    FundingProgramContactRelationFixture::addContact(
      $creationContact['id'],
      $this->fundingProgram->getId(),
      ['view'],
    );

    FundingCaseContactRelationFixture::addContact(
      $creationContact['id'],
      $this->fundingCase->getId(),
      ['application_create', 'application_save'],
    );
  }

}
