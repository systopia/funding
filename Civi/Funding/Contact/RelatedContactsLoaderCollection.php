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
  public function getRelatedContacts(int $contactId, array $contactRelation, ?array $parentContactRelation): array {
    $supportedLoaderFound = FALSE;
    $contacts = [];
    foreach ($this->relatedContactsLoaders as $relatedContactsLoader) {
      if ($relatedContactsLoader->supportsRelation($contactRelation, $parentContactRelation)) {
        $supportedLoaderFound = TRUE;
        $contacts += $relatedContactsLoader->getRelatedContacts($contactId, $contactRelation, $parentContactRelation);
      }
    }

    if (FALSE === $supportedLoaderFound) {
      throw new \InvalidArgumentException(
        sprintf('No supported related contacts loaded found for contact relation with ID %d', $contactRelation['id'])
      );
    }

    return $contacts;
  }

  /**
   * @inheritDoc
   */
  public function supportsRelation(array $contactRelation, ?array $parentContactRelation): bool {
    foreach ($this->relatedContactsLoaders as $relatedContactsLoader) {
      if ($relatedContactsLoader->supportsRelation($contactRelation, $parentContactRelation)) {
        return TRUE;
      }
    }

    return FALSE;
  }

}
