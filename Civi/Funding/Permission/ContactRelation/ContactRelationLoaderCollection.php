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

namespace Civi\Funding\Permission\ContactRelation;

final class ContactRelationLoaderCollection implements ContactRelationLoaderInterface {

  /**
   * @var iterable<\Civi\Funding\Permission\ContactRelation\ContactRelationLoaderInterface>
   */
  private iterable $contactRelationLoaders;

  /**
   * @param iterable<\Civi\Funding\Permission\ContactRelation\ContactRelationLoaderInterface> $contactRelationLoaders
   */
  public function __construct(iterable $contactRelationLoaders) {
    $this->contactRelationLoaders = $contactRelationLoaders;
  }

  /**
   * @inheritDoc
   */
  public function getContacts(string $relationType, array $relationProperties): array {
    $supportedLoaderFound = FALSE;
    $contacts = [];
    foreach ($this->contactRelationLoaders as $contactRelationLoader) {
      if ($contactRelationLoader->supportsRelationType($relationType)) {
        $supportedLoaderFound = TRUE;
        $contacts += $contactRelationLoader->getContacts($relationType, $relationProperties);
      }
    }

    if (!$supportedLoaderFound) {
      throw new \InvalidArgumentException(
        \sprintf('No supported contact relation loader found for contact relation type "%s"', $relationType)
      );
    }

    return $contacts;
  }

  public function supportsRelationType(string $relationType): bool {
    foreach ($this->contactRelationLoaders as $contactRelationChecker) {
      if ($contactRelationChecker->supportsRelationType($relationType)) {
        return TRUE;
      }
    }

    return FALSE;
  }

}
