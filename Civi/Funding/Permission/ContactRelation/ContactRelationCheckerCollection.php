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

final class ContactRelationCheckerCollection implements ContactRelationCheckerInterface {

  /**
   * @var iterable<\Civi\Funding\Permission\ContactRelation\ContactRelationCheckerInterface>
   */
  private iterable $contactRelationCheckers;

  /**
   * @param iterable<\Civi\Funding\Permission\ContactRelation\ContactRelationCheckerInterface> $contactRelationCheckers
   */
  public function __construct(iterable $contactRelationCheckers) {
    $this->contactRelationCheckers = $contactRelationCheckers;
  }

  /**
   * @inheritDoc
   */
  public function hasRelation(int $contactId, string $relationType, array $relationProperties): bool {
    $supportedCheckerFound = FALSE;
    foreach ($this->contactRelationCheckers as $contactRelationChecker) {
      if ($contactRelationChecker->supportsRelationType($relationType)) {
        $supportedCheckerFound = TRUE;
        if ($contactRelationChecker->hasRelation($contactId, $relationType, $relationProperties)) {
          return TRUE;
        }
      }
    }

    if (FALSE === $supportedCheckerFound) {
      throw new \InvalidArgumentException(
        \sprintf('No supported contact relation checker found for contact relation type "%s"', $relationType)
      );
    }

    return FALSE;
  }

  public function supportsRelationType(string $relationType): bool {
    foreach ($this->contactRelationCheckers as $contactRelationChecker) {
      if ($contactRelationChecker->supportsRelationType($relationType)) {
        return TRUE;
      }
    }

    return FALSE;
  }

}
