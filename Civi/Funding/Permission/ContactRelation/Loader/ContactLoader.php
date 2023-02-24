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

namespace Civi\Funding\Permission\ContactRelation\Loader;

use Civi\Funding\Permission\ContactRelation\ContactRelationLoaderInterface;
use Civi\Funding\Permission\ContactRelation\Types\Contact;
use Civi\RemoteTools\Api4\Api4Interface;
use Webmozart\Assert\Assert;

/**
 * Loads the contact with a given contact ID.
 */
final class ContactLoader implements ContactRelationLoaderInterface {

  private Api4Interface $api4;

  public function __construct(Api4Interface $api4) {
    $this->api4 = $api4;
  }

  /**
   * @inheritDoc
   *
   * @throws \API_Exception
   */
  public function getContacts(string $relationType, array $relationProperties): array {
    $contactId = $relationProperties['contactId'];
    Assert::integerish($contactId);

    $action = $this->api4->createGetAction('Contact')
      ->setCheckPermissions(FALSE)
      ->addWhere('id', '=', $contactId);

    /** @phpstan-var array<int, array<string, mixed>> $contacts */
    $contacts = $this->api4->executeAction($action)->indexBy('id')->getArrayCopy();

    return $contacts;
  }

  public function supportsRelationType(string $relationType): bool {
    return Contact::NAME === $relationType;
  }

}
