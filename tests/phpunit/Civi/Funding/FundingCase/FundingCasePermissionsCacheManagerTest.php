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

namespace Civi\Funding\FundingCase;

use Civi\Api4\FundingCasePermissionsCache;
use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingCaseFixture;
use Civi\Funding\Fixtures\FundingCaseTypeFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;
use Civi\RemoteTools\Api4\Api4;

/**
 * @covers \Civi\Funding\FundingCase\FundingCasePermissionsCacheManager
 *
 * @group headless
 */
final class FundingCasePermissionsCacheManagerTest extends AbstractFundingHeadlessTestCase {

  private FundingCasePermissionsCacheManager $permissionsCacheManager;

  protected function setUp(): void {
    parent::setUp();
    $this->permissionsCacheManager = new FundingCasePermissionsCacheManager(Api4::getInstance());
  }

  public function test(): void {
    $fundingProgram = FundingProgramFixture::addFixture();
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $contact = ContactFixture::addIndividual();

    $fundingCase1 = FundingCaseFixture::addFixture(
      $fundingProgram->getId(),
      $fundingCaseType->getId(),
      $contact['id'],
      $contact['id']
    );

    $fundingCase2 = FundingCaseFixture::addFixture(
      $fundingProgram->getId(),
      $fundingCaseType->getId(),
      $contact['id'],
      $contact['id']
    );

    $this->permissionsCacheManager->add($fundingCase1->getId(), 33, TRUE, ['application_create']);

    $permissionsCacheResult = FundingCasePermissionsCache::get()->execute();
    static::assertCount(1, $permissionsCacheResult);
    $permissionsCache1 = [
      'id' => $permissionsCacheResult[0]['id'],
      'funding_case_id' => $fundingCase1->getId(),
      'contact_id' => 33,
      'is_remote' => TRUE,
      'permissions' => ['application_create'],
    ];
    static::assertEquals($permissionsCache1, $permissionsCacheResult[0]);

    $this->permissionsCacheManager->add($fundingCase2->getId(), 44, FALSE, ['review_content']);

    // @phpstan-ignore-next-line
    $this->permissionsCacheManager->update($permissionsCache1['id'], ['new_permission']);
    $permissionsCache1['permissions'] = ['new_permission'];

    $permissionsCacheResult = FundingCasePermissionsCache::get()->execute();
    static::assertCount(2, $permissionsCacheResult);
    static::assertEquals($permissionsCache1, $permissionsCacheResult[0]);

    $permissionsCache2 = [
      'id' => $permissionsCacheResult[1]['id'],
      'funding_case_id' => $fundingCase2->getId(),
      'contact_id' => 44,
      'is_remote' => FALSE,
      'permissions' => ['review_content'],
    ];
    static::assertEquals($permissionsCache2, $permissionsCacheResult[1]);

    $this->permissionsCacheManager->clearByFundingCaseId($fundingCase1->getId());
    $permissionsCache1['permissions'] = NULL;

    $permissionsCacheResult = FundingCasePermissionsCache::get()->execute();
    static::assertCount(2, $permissionsCacheResult);
    static::assertEquals($permissionsCache1, $permissionsCacheResult[0]);
    static::assertEquals($permissionsCache2, $permissionsCacheResult[1]);

    $this->permissionsCacheManager->clearByContactId(44);
    $permissionsCache2['permissions'] = NULL;

    $permissionsCacheResult = FundingCasePermissionsCache::get()->execute();
    static::assertCount(2, $permissionsCacheResult);
    static::assertEquals($permissionsCache1, $permissionsCacheResult[0]);
    static::assertEquals($permissionsCache2, $permissionsCacheResult[1]);

    $this->permissionsCacheManager->deleteByContactId(33);

    $permissionsCacheResult = FundingCasePermissionsCache::get()->execute();
    static::assertCount(1, $permissionsCacheResult);
    static::assertEquals($permissionsCache2, $permissionsCacheResult[0]);

    $this->permissionsCacheManager->clear();

    $permissionsCacheResult = FundingCasePermissionsCache::get()->execute();
    static::assertCount(0, $permissionsCacheResult);
  }

}
