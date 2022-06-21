<?php
declare(strict_types = 1);

namespace Civi\Funding\Remote;

interface RemoteFundingEntityManagerInterface {

  /**
   * @return array<string, mixed>|null
   *
   * @throws \API_Exception
   */
  public function getById(string $entity, int $id, string $remoteContactId): ?array;

  /**
   * @throws \API_Exception
   */
  public function hasAccess(string $entity, int $id, string $remoteContactId): bool;

}
