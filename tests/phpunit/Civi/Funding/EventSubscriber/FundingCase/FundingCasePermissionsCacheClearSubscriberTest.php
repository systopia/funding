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

namespace Civi\Funding\EventSubscriber\FundingCase;

use Civi\Api4\Contact;
use Civi\Api4\FundingCaseContactRelation;
use Civi\Api4\FundingCasePermissionsCache;
use Civi\Api4\Group;
use Civi\Api4\GroupContact;
use Civi\Api4\Relationship;
use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\ContactTypeFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Fixtures\FundingCaseFixture;
use Civi\Funding\Fixtures\FundingCasePermissionsCacheFixture;
use Civi\Funding\Fixtures\FundingCaseTypeFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;
use Civi\Funding\Fixtures\GroupContactFixture;
use Civi\Funding\Fixtures\GroupFixture;
use Civi\Funding\Permission\ContactRelation\Types\ContactTypeAndGroup;
use Civi\Funding\Permission\ContactRelation\Types\Relationship as RelationshipType;

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
      'hook_civicrm_pre::Individual' => ['onPreContact', PHP_INT_MIN],
      'hook_civicrm_pre::Organization' => ['onPreContact', PHP_INT_MIN],
      'hook_civicrm_pre::Household' => ['onPreContact', PHP_INT_MIN],
      'hook_civicrm_pre::Group' => ['onPreGroup', PHP_INT_MIN],
      'hook_civicrm_pre::GroupContact' => ['onPreGroupContact', PHP_INT_MIN],
      'hook_civicrm_pre::FundingCaseContactRelation' => ['onPreFundingCaseContactRelation', PHP_INT_MIN],
      'hook_civicrm_pre::Relationship' => ['onPreRelationship', PHP_INT_MIN],
    ];

    static::assertEquals($expectedSubscriptions, FundingCasePermissionsCacheClearSubscriber::getSubscribedEvents());

    foreach ($expectedSubscriptions as [$method]) {
      static::assertTrue(method_exists(FundingCasePermissionsCacheClearSubscriber::class, $method));
    }
  }

  public function testOnPreContact(): void {
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

  public function testOnPreGroup(): void {
    $fundingProgram = FundingProgramFixture::addFixture();
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $contact = ContactFixture::addIndividual();

    $group1 = GroupFixture::addFixture();
    $group2 = GroupFixture::addFixture();
    $group3 = GroupFixture::addFixture();

    $fundingCase = FundingCaseFixture::addFixture(
      $fundingProgram->getId(),
      $fundingCaseType->getId(),
      $contact['id'],
      $contact['id']
    );
    FundingCaseContactRelationFixture::addFixture($fundingCase->getId(), RelationshipType::NAME, [
      'relationshipTypeIds' => [],
      'contactTypeIds' => [],
      'groupIds' => [$group1['id']],
    ], ['permission']);
    FundingCasePermissionsCacheFixture::add($fundingCase->getId(), $contact['id'], FALSE, ['test']);

    // Changing title should not affect cache.
    $group1['title'] = 'new title';
    Group::update(FALSE)->setValues($group1)->execute();
    static::assertSame(['test'], FundingCasePermissionsCache::get(FALSE)->execute()->single()['permissions']);

    // Changing is_active of unrelated group should not affect cache.
    $group2['is_active'] = FALSE;
    Group::update(FALSE)->setValues($group2)->execute();
    static::assertSame(['test'], FundingCasePermissionsCache::get(FALSE)->execute()->single()['permissions']);

    // Changing is_active should affect cache.
    $group1['is_active'] = FALSE;
    Group::update(FALSE)->setValues($group1)->execute();
    static::assertNull(FundingCasePermissionsCache::get(FALSE)->execute()->single()['permissions']);

    FundingCasePermissionsCache::delete(FALSE)->addWhere('id', 'IS NOT NULL')->execute();
    FundingCasePermissionsCacheFixture::add($fundingCase->getId(), $contact['id'], FALSE, ['test']);

    // Deleting unrelated group should not affect cache.
    Group::delete(FALSE)->addWhere('id', '=', $group2['id'])->execute();
    static::assertSame(['test'], FundingCasePermissionsCache::get(FALSE)->execute()->single()['permissions']);

    // Deleting group should affect cache.
    Group::delete(FALSE)->addWhere('id', '=', $group1['id'])->execute();
    static::assertNull(FundingCasePermissionsCache::get(FALSE)->execute()->single()['permissions']);

    // Deleting group should affect cache when there is no group restriction.
    FundingCaseContactRelationFixture::addFixture($fundingCase->getId(), ContactTypeAndGroup::NAME, [
      'contactTypeIds' => [],
      'groupIds' => [],
    ], ['permission']);
    FundingCasePermissionsCache::delete(FALSE)->addWhere('id', 'IS NOT NULL')->execute();
    FundingCasePermissionsCacheFixture::add($fundingCase->getId(), $contact['id'], FALSE, ['test']);
    Group::delete(FALSE)->addWhere('id', '=', $group3['id'])->execute();
    static::assertNull(FundingCasePermissionsCache::get(FALSE)->execute()->single()['permissions']);
  }

  public function testOnPreGroupContact(): void {
    $fundingProgram = FundingProgramFixture::addFixture();
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $contact1 = ContactFixture::addIndividual();
    $contact2 = ContactFixture::addIndividual();

    $group1 = GroupFixture::addFixture();
    $group2 = GroupFixture::addFixture();
    $group3 = GroupFixture::addFixture();

    $fundingCase1 = FundingCaseFixture::addFixture(
      $fundingProgram->getId(),
      $fundingCaseType->getId(),
      $contact1['id'],
      $contact1['id']
    );
    $fundingCase2 = FundingCaseFixture::addFixture(
      $fundingProgram->getId(),
      $fundingCaseType->getId(),
      $contact1['id'],
      $contact1['id']
    );
    FundingCaseContactRelationFixture::addFixture($fundingCase1->getId(), RelationshipType::NAME, [
      'relationshipTypeIds' => [],
      'contactTypeIds' => [],
      // IDs might be persisted as strings.
      'groupIds' => [(string) $group1['id']],
    ], ['permission']);
    FundingCaseContactRelationFixture::addFixture($fundingCase2->getId(), RelationshipType::NAME, [
      'relationshipTypeIds' => [],
      'contactTypeIds' => [],
      'groupIds' => [$group2['id']],
    ], ['permission']);

    FundingCasePermissionsCacheFixture::add($fundingCase1->getId(), $contact1['id'], FALSE, ['test']);

    // Adding contact to group should affect cache.
    $groupContact = GroupContactFixture::addFixtureWithGroupId($group1['id'], $contact1['id']);
    static::assertNull(FundingCasePermissionsCache::get(FALSE)->execute()->single()['permissions']);

    // Clear permissions and add new one.
    FundingCasePermissionsCache::delete(FALSE)->addWhere('id', 'IS NOT NULL')->execute();
    FundingCasePermissionsCacheFixture::add($fundingCase1->getId(), $contact1['id'], FALSE, ['test']);

    // Removing contact from group should affect cache.
    GroupContact::delete(FALSE)->addWhere('id', '=', $groupContact['id'])->execute();
    static::assertNull(FundingCasePermissionsCache::get(FALSE)->execute()->single()['permissions']);

    // Clear permissions and add new one.
    FundingCasePermissionsCache::delete(FALSE)->addWhere('id', 'IS NOT NULL')->execute();
    FundingCasePermissionsCacheFixture::add($fundingCase1->getId(), $contact1['id'], FALSE, ['test']);
    FundingCasePermissionsCacheFixture::add($fundingCase1->getId(), $contact2['id'], FALSE, ['test']);

    $groupContact = GroupContactFixture::addFixtureWithGroupId($group1['id'], $contact1['id']);

    // Updating GroupContact should affect cache.
    $groupContact['contact_id'] = $contact2['id'];
    $groupContact['group_id'] = $group2['id'];
    GroupContact::update(FALSE)->setValues($groupContact)->execute();

    static::assertSame([NULL, NULL], FundingCasePermissionsCache::get(FALSE)->execute()->column('permissions'));

    // Adding GroupContact should affect cache when there is no group restriction.
    FundingCaseContactRelationFixture::addFixture($fundingCase1->getId(), ContactTypeAndGroup::NAME, [
      'contactTypeIds' => [],
      'groupIds' => [],
    ], ['permission']);
    FundingCasePermissionsCache::delete(FALSE)->addWhere('id', 'IS NOT NULL')->execute();
    FundingCasePermissionsCacheFixture::add($fundingCase1->getId(), $contact1['id'], FALSE, ['test']);
    GroupContactFixture::addFixtureWithGroupId($group1['id'], $contact1['id']);
    static::assertNull(FundingCasePermissionsCache::get(FALSE)->execute()->single()['permissions']);
  }

  public function testOnPreGroupContactViaBAO(): void {
    $fundingProgram = FundingProgramFixture::addFixture();
    $fundingCaseType = FundingCaseTypeFixture::addFixture();
    $contact1 = ContactFixture::addIndividual();

    $group1 = GroupFixture::addFixture();
    $group2 = GroupFixture::addFixture();

    $fundingCase1 = FundingCaseFixture::addFixture(
      $fundingProgram->getId(),
      $fundingCaseType->getId(),
      $contact1['id'],
      $contact1['id']
    );
    $fundingCase2 = FundingCaseFixture::addFixture(
      $fundingProgram->getId(),
      $fundingCaseType->getId(),
      $contact1['id'],
      $contact1['id']
    );
    FundingCaseContactRelationFixture::addFixture($fundingCase1->getId(), RelationshipType::NAME, [
      'relationshipTypeIds' => [],
      'contactTypeIds' => [],
      'groupIds' => [$group1['id']],
    ], ['permission']);
    FundingCaseContactRelationFixture::addFixture($fundingCase2->getId(), RelationshipType::NAME, [
      'relationshipTypeIds' => [],
      'contactTypeIds' => [],
      'groupIds' => [$group2['id']],
    ], ['permission']);

    FundingCasePermissionsCacheFixture::add($fundingCase1->getId(), $contact1['id'], FALSE, ['test']);

    // Adding contact to group should affect cache.
    \CRM_Contact_BAO_GroupContact::addContactsToGroup([(string) $contact1['id']], $group1['id']);
    static::assertNull(FundingCasePermissionsCache::get(FALSE)->execute()->single()['permissions']);

    // Clear permissions and add new one.
    FundingCasePermissionsCache::delete(FALSE)->addWhere('id', 'IS NOT NULL')->execute();
    FundingCasePermissionsCacheFixture::add($fundingCase1->getId(), $contact1['id'], FALSE, ['test']);

    // Removing contact from group should affect cache.
    // Extra variable because passed as reference.
    $contactIds = [(string) $contact1['id']];
    \CRM_Contact_BAO_GroupContact::removeContactsFromGroup($contactIds, $group1['id']);
    static::assertNull(FundingCasePermissionsCache::get(FALSE)->execute()->single()['permissions']);
  }

  public function testOnPreFundingCaseContactRelation(): void {
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

  public function testOnPreRelationship(): void {
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
