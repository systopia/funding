<?php
declare(strict_types = 1);

namespace Civi\Funding\EventSubscriber;

use Civi\Funding\Event\RemoteFundingDAOGetEvent;
use Civi\RemoteTools\Event\DAOGetEvent;
use Civi\RemoteTools\EventSubscriber\AbstractRemoteDAOGetSubscriber;
use Webmozart\Assert\Assert;

final class RemoteFundingCaseDAOGetSubscriber extends AbstractRemoteDAOGetSubscriber {

  protected const DAO_ENTITY_NAME = 'FundingCase';

  protected const ENTITY_NAME = 'RemoteFundingCase';

  protected const EVENT_CLASS = RemoteFundingDAOGetEvent::class;

  public function onGet(DAOGetEvent $event): void {
    /** @var \Civi\Funding\Event\RemoteFundingDAOGetEvent $event */
    parent::onGet($event);

    $event->setRecords($this->handlePermissions($event->getRecords()));
  }

  /**
   * @param array<array<string, mixed>> $records
   *
   * @return array<array<string, mixed>>
   */
  private function handlePermissions(array $records): array {
    foreach ($records as &$record) {
      Assert::isArray($record['permissions']);
      $record['permissions'] = $this->mergePermissions(
        $this->jsonEncodePermissions($record['permissions'])
      );

      foreach ($record['permissions'] as $permission) {
        $record['PERM_' . $permission] = TRUE;
      }
    }

    return $records;
  }

  /**
   * @param string[] $permissions
   *
   * @return array<string[]>
   */
  private function jsonEncodePermissions(array $permissions): array {
    /** @var array<string[]> $permissions */
    $permissions = array_map('json_decode', $permissions);

    return $permissions;
  }

  /**
   * @param array<string[]> $permissions
   *
   * @return string[]
   */
  private function mergePermissions(array $permissions): array {
    return array_values(array_unique(
      array_reduce($permissions, fn(array $p1, array $p2): array => array_merge($p1, $p2), [])
    ));
  }

}
