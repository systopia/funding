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

use Civi\Api4\FundingCaseContactRelation;
use Civi\Core\Event\PreEvent;
use Civi\Funding\Database\ChangeSetFactory;
use Civi\Funding\Entity\FundingCaseContactRelationEntity;
use Civi\Funding\FundingCase\FundingCasePermissionsCacheManager;
use Civi\Funding\Permission\ContactRelation\Types\ContactTypeAndGroup;
use Civi\Funding\Permission\ContactRelation\Types\Relationship;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\Api4\Query\Comparison;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Clears cached funding case permissions, if an entity is
 * created/updated/deleted that might affect the permissions.
 *
 * @todo Handle contact merge: No event is dispatched in
 * \CRM_Contact_BAO_GroupContact::mergeGroupContact().
 */
final class FundingCasePermissionsCacheClearSubscriber implements EventSubscriberInterface {

  private const RELATION_TYPES_WITH_GROUP_IDS = [Relationship::NAME, ContactTypeAndGroup::NAME];

  private Api4Interface $api4;

  private ChangeSetFactory $changeSetFactory;

  private FundingCasePermissionsCacheManager $permissionsCacheManager;

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents(): array {
    // Use minimum priority, so we also get possible changes by previous event listeners.
    return [
      'hook_civicrm_pre::Individual' => ['onPreContact', PHP_INT_MIN],
      'hook_civicrm_pre::Organization' => ['onPreContact', PHP_INT_MIN],
      'hook_civicrm_pre::Household' => ['onPreContact', PHP_INT_MIN],
      'hook_civicrm_pre::Group' => ['onPreGroup', PHP_INT_MIN],
      'hook_civicrm_pre::GroupContact' => ['onPreGroupContact', PHP_INT_MIN],
      'hook_civicrm_pre::Relationship' => ['onPreRelationship', PHP_INT_MIN],
      'hook_civicrm_pre::FundingCaseContactRelation' => ['onPreFundingCaseContactRelation', PHP_INT_MIN],
    ];
  }

  public function __construct(
    Api4Interface $api4,
    ChangeSetFactory $changeSetFactory,
    FundingCasePermissionsCacheManager $permissionsCacheManager
  ) {
    $this->api4 = $api4;
    $this->changeSetFactory = $changeSetFactory;
    $this->permissionsCacheManager = $permissionsCacheManager;
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onPreContact(PreEvent $event): void {
    $changeSet = $this->changeSetFactory->createChangeSetForPreEvent(
      $event,
      ['contact_type', 'contact_sub_type', 'is_deleted']
    );

    if (isset($changeSet['is_deleted']) || 'delete' === $event->action) {
      $this->permissionsCacheManager->deleteByContactId((int) $event->id);
    }
    elseif (isset($changeSet['contact_type']) || isset($changeSet['contact_sub_type'])) {
      $this->permissionsCacheManager->clearByContactId((int) $event->id);
    }
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onPreGroup(PreEvent $event): void {
    if (NULL === $event->id) {
      return;
    }

    if ('delete' !== $event->action) {
      $changeSet = $this->changeSetFactory->createChangeSetForPreEvent($event, ['is_active']);
      if (!isset($changeSet['is_active'])) {
        return;
      }
    }

    foreach ($this->getRelationshipRelationsByGroupId($event->id) as $relation) {
      $this->permissionsCacheManager->clearByFundingCaseId($relation->getFundingCaseId());
    }
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onPreGroupContact(PreEvent $event): void {
    if ('create' === $event->action) {
      if (!isset($event->params['contact_id'])) {
        // \CRM_Contact_BAO_GroupContact::bulkAddContactsToGroup()
        // The BAO class has an unexpected way of defining the event.
        $contactIds = $event->params;
        $groupIds = [(int) $event->id];
      }
      else {
        // APIv4
        $contactIds = [(int) $event->params['contact_id']];
        $groupIds = [(int) $event->params['group_id']];
      }
    }
    elseif ('delete' === $event->action) {
      if (isset($event->params[0])) {
        // \CRM_Contact_BAO_GroupContact::removeContactsFromGroup()
        // The BAO class has an unexpected way of defining the event.
        $contactIds = $event->params;
        $groupIds = [(int) $event->id];
      }
      else {
        // APIv4
        /** @phpstan-var array{id: int, contact_id: int, group_id: int} $oldValues */
        $oldValues = $this->api4->getEntity('GroupContact', (int) $event->id);
        $contactIds = [$oldValues['contact_id']];
        $groupIds = [$oldValues['group_id']];
      }
    }
    else {
      // APIv4
      /** @phpstan-var array{id: int, contact_id: int, group_id: int} $oldValues */
      $oldValues = $this->api4->getEntity('GroupContact', (int) $event->id);
      $contactIds = [$oldValues['contact_id']];
      if (isset($event->params['contact_id']) && (int) $event->params['contact_id'] !== $oldValues['contact_id']) {
        $contactIds[] = (int) $event->params['contact_id'];
      }

      $groupIds = [$oldValues['group_id']];
      if (isset($event->params['group_id']) && (int) $event->params['group_id'] !== $oldValues['group_id']) {
        $groupIds[] = (int) $event->params['group_id'];
      }
    }

    foreach ($groupIds as $groupId) {
      foreach ($this->getRelationshipRelationsByGroupId($groupId) as $relation) {
        $this->permissionsCacheManager->clearByFundingCaseIdAndContactIds($relation->getFundingCaseId(), $contactIds);
      }
    }
  }

  /**
   * @throws \CRM_Core_Exception
   *
   * phpcs:disable Generic.Metrics.CyclomaticComplexity.TooHigh
   */
  public function onPreRelationship(PreEvent $event): void {
  // phpcs:enable
    $params = $event->params;
    // Contact ID in $params might be a string.
    if (isset($params['contact_id_a'])) {
      $params['contact_id_a'] = (int) $params['contact_id_a'];
    }
    if (isset($params['contact_id_b'])) {
      $params['contact_id_b'] = (int) $params['contact_id_b'];
    }

    if (NULL === $event->id) {
      if ('create' === $event->action) {
        $this->permissionsCacheManager->clearByContactId(
          $params['contact_id_a'],
          $params['contact_id_b']
        );
      }

      return;
    }

    /**
     * @phpstan-var array{
     *   contact_id_a: int,
     *   contact_id_b: int,
     *   relationship_type_id: int,
     *   is_active: bool,
     * } $oldValues
     */
    $oldValues = $this->api4->getEntity('Relationship', (int) $event->id, ['checkPermissions' => FALSE]);
    $newValues = $params + $oldValues;

    if ('delete' === $event->action) {
      $this->permissionsCacheManager->clearByContactId($oldValues['contact_id_a'], $oldValues['contact_id_b']);
    }
    elseif ($oldValues['is_active'] !== $newValues['is_active']) {
      if ($newValues['is_active']) {
        $this->permissionsCacheManager->clearByContactId($newValues['contact_id_a'], $newValues['contact_id_b']);
      }
      else {
        $this->permissionsCacheManager->clearByContactId($oldValues['contact_id_a'], $oldValues['contact_id_b']);
      }
    }
    elseif ($oldValues['relationship_type_id'] !== $newValues['relationship_type_id']) {
      $this->permissionsCacheManager->clearByContactId(
        $oldValues['contact_id_a'],
        $oldValues['contact_id_b'],
        $newValues['contact_id_a'],
        $newValues['contact_id_b'],
      );
    }
    else {
      if ($oldValues['contact_id_a'] !== $newValues['contact_id_a']) {
        $this->permissionsCacheManager->clearByContactId($newValues['contact_id_a'], $oldValues['contact_id_a']);
      }
      if ($oldValues['contact_id_b'] !== $newValues['contact_id_b']) {
        $this->permissionsCacheManager->clearByContactId($newValues['contact_id_b'], $oldValues['contact_id_b']);
      }
    }
  }

  /**
   * @throws \CRM_Core_Exception
   */
  public function onPreFundingCaseContactRelation(PreEvent $event): void {
    if (isset($event->params['funding_case_id'])) {
      $this->permissionsCacheManager->clearByFundingCaseId((int) $event->params['funding_case_id']);
    }
    elseif (NULL !== $event->id) {
      $fundingCaseId = $this->api4->execute(FundingCaseContactRelation::getEntityName(), 'get', [
        'select' => ['funding_case_id'],
        'where' => [['id', '=', $event->id]],
        'checkPermissions' => FALSE,
      ])->single()['funding_case_id'];
      $this->permissionsCacheManager->clearByFundingCaseId($fundingCaseId);
    }
  }

  /**
   * @phpstan-return iterable<FundingCaseContactRelationEntity>
   *
   * @throws \CRM_Core_Exception
   */
  private function getRelationshipRelationsByGroupId(int $groupId): iterable {
    $relations = FundingCaseContactRelationEntity::allFromApiResult($this->api4->getEntities(
      FundingCaseContactRelation::getEntityName(),
      Comparison::new('type', 'IN', self::RELATION_TYPES_WITH_GROUP_IDS)
    ));

    foreach ($relations as $relation) {
      // Group id integers might be persisted as strings.
      /** @phpstan-var list<int|string> $relationGroupIds */
      $relationGroupIds = $relation->getProperty('groupIds', []);
      // @phpstan-ignore function.strict
      if ([] === $relationGroupIds || in_array($groupId, $relationGroupIds, FALSE)) {
        yield $relation;
      }
    }
  }

}
