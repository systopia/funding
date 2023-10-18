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

namespace phpunit\Civi\Funding\EventSubscriber\FundingCase;

use Civi\Api4\Contact;
use Civi\Api4\FundingCaseContactRelation;
use Civi\Api4\FundingCasePermissionsCache;
use Civi\Api4\Relationship;
use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\EventSubscriber\FundingCase\FundingCasePermissionsCacheClearSubscriber;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\ContactTypeFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Fixtures\FundingCaseFixture;
use Civi\Funding\Fixtures\FundingCasePermissionsCacheFixture;
use Civi\Funding\Fixtures\FundingCaseTypeFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;

/**
 * @covers \Civi\Funding\EventSubscriber\FundingCase\FundingCasePermissionsCacheClearSubscriber
 *
 * @group headless
 */
final class FundingCasePermissionsCacheClearSubscriberTest extends AbstractFundingHeadlessTestCase {

  protected function setUp(): void {
    parent::setUp();
  }

  public function testGetSubscribedEvents(): void {
    $expectedSubscriptions = [
      'hook_civicrm_pre::Individual' => ['preContact', PHP_INT_MIN],
      'hook_civicrm_pre::Organization' => ['preContact', PHP_INT_MIN],
      'hook_civicrm_pre::Household' => ['preContact', PHP_INT_MIN],
      'hook_civicrm_pre::FundingCaseContactRelation' => ['preFundingCaseContactRelation', PHP_INT_MIN],
      'hook_civicrm_pre::Relationship' => ['preRelationship', PHP_INT_MIN],
    ];

    static::assertEquals($expectedSubscriptions, FundingCasePermissionsCacheClearSubscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as [$method, $priority]) {
      static::assertTrue(method_exists(FundingCasePermissionsCacheClearSubscriber::class, $method));
    }
  }

  public function testPreContact(): void {
    $fundingProgram = FundingProgramFixture::addFixture();
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $contact = ContactFixture::addIndividual();

    $fundingCase = FundingCaseFixture::addFixture(
      $fundingProgram->getId(),
      $fundingCaseType->getId(),
      $contact['id'],
      $contact['id']
    );
    FundingCasePermissionsCacheFixture::add($fundingCase->getId(), $contact['id'], FALSE, ['test']);

    // Changing first name should not affect cache.
    Contact::update(FALSE)
      ->addValue('first_name', 'New')
      ->addWhere('id', '=', $contact['id'])
      ->execute();
    static::assertSame(['test'], FundingCasePermissionsCache::get(FALSE)->execute()->single()['permissions']);

    // Changing contact sub type should clear cache.
    ContactTypeFixture::addIndividualFixture('SubContactType');
    Contact::update(FALSE)
      ->addValue('contact_sub_type', 'SubContactType')
      ->addWhere('id', '=', $contact['id'])
      ->execute();
    static::assertNull(FundingCasePermissionsCache::get(FALSE)->execute()->single()['permissions']);

    // Deleting contact should delete cache entry.
    Contact::delete(FALSE)
      ->addWhere('id', '=', $contact['id'])
      ->execute();
    static::assertCount(0, FundingCasePermissionsCache::get(FALSE)->execute());
  }

  public function testPreFundingCaseContactRelation(): void {
    $fundingProgram = FundingProgramFixture::addFixture();
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $contact = ContactFixture::addIndividual();

    $fundingCase = FundingCaseFixture::addFixture(
      $fundingProgram->getId(),
      $fundingCaseType->getId(),
      $contact['id'],
      $contact['id']
    );

    FundingCasePermissionsCacheFixture::add($fundingCase->getId(), $contact['id'], FALSE, ['test']);

    // Adding funding case contact relation should clear cache.
    $contactRelation = FundingCaseContactRelationFixture::addFixture(
      $fundingCase->getId(),
      'Relationship',
      [
        'relationshipTypeId' => 1,
      ],
      ['test']
    );

    static::assertNull(FundingCasePermissionsCache::get(FALSE)->execute()->single()['permissions']);

    FundingCasePermissionsCache::update(FALSE)
      ->addValue('permissions', ['test'])
      ->addWhere('id', 'IS NOT NULL')
      ->execute();

    // Changing contact relation should clear cache.
    FundingCaseContactRelation::update(FALSE)
      ->addValue('permissions', ['new'])
      ->addWhere('id', '=', $contactRelation['id'])
      ->execute();

    static::assertNull(FundingCasePermissionsCache::get(FALSE)->execute()->single()['permissions']);

    FundingCasePermissionsCache::update(FALSE)
      ->addValue('permissions', ['test'])
      ->addWhere('id', 'IS NOT NULL')
      ->execute();

    // Deleting contact relation should clear cache.
    FundingCaseContactRelation::delete(FALSE)
      ->addWhere('id', '=', $contactRelation['id'])
      ->execute();

    static::assertNull(FundingCasePermissionsCache::get(FALSE)->execute()->single()['permissions']);
  }

  public function testPreRelationship(): void {
    $fundingProgram = FundingProgramFixture::addFixture();
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $contact1 = ContactFixture::addIndividual();
    $contact2 = ContactFixture::addIndividual();
    $contact3 = ContactFixture::addIndividual();
    $contact4 = ContactFixture::addIndividual();

    $fundingCase = FundingCaseFixture::addFixture(
      $fundingProgram->getId(),
      $fundingCaseType->getId(),
      $contact1['id'],
      $contact1['id']
    );
    FundingCasePermissionsCacheFixture::add($fundingCase->getId(), $contact1['id'], FALSE, ['test']);
    FundingCasePermissionsCacheFixture::add($fundingCase->getId(), $contact2['id'], TRUE, ['test']);
    FundingCasePermissionsCacheFixture::add($fundingCase->getId(), $contact3['id'], FALSE, ['test']);

    // Adding a relationship should clear cache.
    $relationship = Relationship::create(FALSE)
      ->setValues([
        'contact_id_a' => $contact1['id'],
        'contact_id_b' => $contact2['id'],
        'relationship_type_id' => 1,
      ])->execute()->first();

    $permissionsCacheResult = FundingCasePermissionsCache::get(FALSE)->execute();
    static::assertNull($permissionsCacheResult[0]['permissions']);
    static::assertNull($permissionsCacheResult[1]['permissions']);
    static::assertSame(['test'], $permissionsCacheResult[2]['permissions']);

    FundingCasePermissionsCache::update(FALSE)
      ->addValue('permissions', ['test'])
      ->addWhere('id', 'IS NOT NULL')
      ->execute();

    FundingCasePermissionsCacheFixture::add($fundingCase->getId(), $contact4['id'], TRUE, ['test']);

    // Changing relationship contact should clear cache.
    Relationship::update(FALSE)
      ->addValue('contact_id_a', $contact3['id'])
      ->addWhere('id', '=', $relationship['id'])
      ->execute();

    $permissionsCacheResult = FundingCasePermissionsCache::get(FALSE)->execute();
    static::assertNull($permissionsCacheResult[0]['permissions']);
    static::assertNull($permissionsCacheResult[1]['permissions']);
    static::assertNull($permissionsCacheResult[2]['permissions']);
    static::assertSame(['test'], $permissionsCacheResult[3]['permissions']);

    FundingCasePermissionsCache::update(FALSE)
      ->addValue('permissions', ['test'])
      ->addWhere('id', 'IS NOT NULL')
      ->execute();

    // Changing relationship type should clear cache.
    Relationship::update(FALSE)
      ->addValue('relationship_type_id', 2)
      ->addWhere('id', '=', $relationship['id'])
      ->execute();

    $permissionsCacheResult = FundingCasePermissionsCache::get(FALSE)->execute();
    static::assertSame(['test'], $permissionsCacheResult[0]['permissions']);
    static::assertNull($permissionsCacheResult[1]['permissions']);
    static::assertNull($permissionsCacheResult[2]['permissions']);
    static::assertSame(['test'], $permissionsCacheResult[3]['permissions']);

    FundingCasePermissionsCache::update(FALSE)
      ->addValue('permissions', ['test'])
      ->addWhere('id', 'IS NOT NULL')
      ->execute();

    // Deleting relationship should clear cache.
    Relationship::delete(FALSE)
      ->addWhere('id', '=', $relationship['id'])
      ->execute();

    $permissionsCacheResult = FundingCasePermissionsCache::get(FALSE)->execute();
    static::assertSame(['test'], $permissionsCacheResult[0]['permissions']);
    static::assertNull($permissionsCacheResult[1]['permissions']);
    static::assertNull($permissionsCacheResult[2]['permissions']);
    static::assertSame(['test'], $permissionsCacheResult[3]['permissions']);
  }

}
