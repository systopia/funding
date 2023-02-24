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

namespace Civi\Api4\Traits;

use Civi\Api4\Contact;
use Civi\Api4\FundingCaseTypeProgram;
use Civi\Funding\Fixtures\ContactTypeFixture;
use Civi\Funding\Fixtures\FundingCaseTypeFixture;
use Civi\Funding\Fixtures\FundingProgramContactRelationFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;

trait FundingCaseTypeFixturesTrait {

  protected int $fundingProgramId;

  protected int $fundingProgramIdWithoutFundingCaseType;

  protected int $permittedContactId;

  protected int $notPermittedContactId;

  /**
   * @throws \API_Exception
   */
  protected function addFixtures(): void {
    $this->fundingProgramId = FundingProgramFixture::addFixture()->getId();
    $this->fundingProgramIdWithoutFundingCaseType =
      FundingProgramFixture::addFixture(['title' => 'Foo', 'abbreviation' => 'FOO'])->getId();

    $permittedContactType = ContactTypeFixture::addIndividualFixture(
      'Permitted', 'permitted');
    $permittedContactTypeId = $permittedContactType['id'];

    FundingProgramContactRelationFixture::addFixture(
      $this->fundingProgramId,
      'ContactType',
      ['contactTypeId' => $permittedContactTypeId],
      ['application_foo', 'application_bar', 'review_baz'],
    );

    FundingProgramContactRelationFixture::addFixture(
      $this->fundingProgramIdWithoutFundingCaseType,
      'ContactType',
      ['contactTypeId' => $permittedContactTypeId],
      ['application_foo', 'application_bar', 'review_baz'],
    );

    $permittedContact = Contact::create()
      ->setCheckPermissions(FALSE)
      ->setValues([
        'contact_type' => 'Individual',
        'contact_sub_type' => 'Permitted',
        'first_name' => 'Permitted',
        'last_name' => 'User',
      ])
      ->execute()->first();
    $this->permittedContactId = $permittedContact['id'];

    $this->notPermittedContactId = Contact::create()
      ->setCheckPermissions(FALSE)
      ->setValues([
        'contact_type' => 'Individual',
        'first_name' => 'NotPermitted',
        'last_name' => 'User',
      ])
      ->execute()->first()['id'];

    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    FundingCaseTypeProgram::create()
      ->setCheckPermissions(FALSE)
      ->setValues([
        'funding_program_id' => $this->fundingProgramId,
        'funding_case_type_id' => $fundingCaseType->getId(),
      ])
      ->execute();
  }

}
