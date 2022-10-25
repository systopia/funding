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

namespace Civi\Funding\Contact;

final class RelatedContactsLoaderCollection implements RelatedContactsLoaderInterface {

  /**
   * @phpstan-var iterable<RelatedContactsLoaderInterface>
   */
  private iterable $relatedContactsLoaders;

  /**
   * @phpstan-param iterable<RelatedContactsLoaderInterface> $relatedContactsLoaders
   */
  public function __construct(iterable $relatedContactsLoaders) {
    $this->relatedContactsLoaders = $relatedContactsLoaders;
  }

  /**
   * @inheritDoc
   */
  public function getRelatedContacts(int $contactId, string $relationType, array $relationProperties): array {
    $supportedLoaderFound = FALSE;
    $contacts = [];
    foreach ($this->relatedContactsLoaders as $relatedContactsLoader) {
      if ($relatedContactsLoader->supportsRelationType($relationType)) {
        $supportedLoaderFound = TRUE;
        $contacts += $relatedContactsLoader->getRelatedContacts($contactId, $relationType, $relationProperties);
      }
    }

    if (FALSE === $supportedLoaderFound) {
      throw new \InvalidArgumentException(
        sprintf('No supported related contacts loader found for contact relation type "%s"', $relationType)
      );
    }

    return $contacts;
  }

  public function supportsRelationType(string $relationType): bool {
    foreach ($this->relatedContactsLoaders as $relatedContactsLoader) {
      if ($relatedContactsLoader->supportsRelationType($relationType)) {
        return TRUE;
      }
    }

    return FALSE;
  }

}
