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

namespace Civi\Funding\Api4\Action\Remote\FundingCase;

use Civi\Funding\AbstractRemoteFundingHeadlessTestCase;
use Civi\Funding\Entity\FundingCaseTypeEntity;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingCaseTypeFixture;
use Civi\Funding\Fixtures\FundingNewCasePermissionsFixture;
use Civi\Funding\Fixtures\FundingProgramContactRelationFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;

abstract class AbstractNewFormActionTestCase extends AbstractRemoteFundingHeadlessTestCase {

  protected FundingCaseTypeEntity $fundingCaseType;

  protected FundingProgramEntity $fundingProgram;

  protected string $remoteContactId;

  /**
   * @throws \CRM_Core_Exception
   */
  protected function initFixtures(): void {
    $this->fundingProgram = FundingProgramFixture::addFixture();
    $this->fundingCaseType = FundingCaseTypeFixture::addFixture();
    $creationContact = ContactFixture::addIndividual();
    $this->remoteContactId = (string) $creationContact['id'];

    FundingProgramContactRelationFixture::addContact(
      $creationContact['id'],
      $this->fundingProgram->getId(),
      ['view'],
    );
    FundingNewCasePermissionsFixture::addCreationContact($this->fundingProgram->getId(), ['application_save']);
  }

}
