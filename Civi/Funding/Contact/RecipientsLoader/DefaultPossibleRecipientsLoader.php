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

namespace Civi\Funding\Contact\RecipientsLoader;

use Civi\Api4\FundingRecipientContactRelation;
use Civi\Funding\Api4\Util\ContactUtil;
use Civi\Funding\Contact\PossibleRecipientsLoaderInterface;
use Civi\Funding\Contact\RelatedContactsLoaderInterface;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\RemoteTools\Api4\Api4Interface;

/**
 * @phpstan-type contactRelationT array{
 *   id: int,
 *   type: string,
 *   properties: array<string, mixed>,
 * }
 */
final class DefaultPossibleRecipientsLoader implements PossibleRecipientsLoaderInterface {

  private Api4Interface $api4;

  private RelatedContactsLoaderInterface $relatedContactsLoader;

  public function __construct(Api4Interface $api4, RelatedContactsLoaderInterface $relatedContactsLoader) {
    $this->api4 = $api4;
    $this->relatedContactsLoader = $relatedContactsLoader;
  }

  public function getPossibleRecipients(int $contactId, FundingProgramEntity $fundingProgram): array {
    $contacts = $this->getRelatedContacts($contactId, $fundingProgram);
    $possibleRecipients = [];
    /** @phpstan-var array{id: int, display_name: ?string} $contact */
    foreach ($contacts as $id => $contact) {
      $possibleRecipients[$id] = ContactUtil::getDisplayName($contact);
    }

    return $possibleRecipients;
  }

  /**
   * @return array<int, array<string, mixed>>
   *
   * @throws \API_Exception
   */
  private function getRelatedContacts(int $contactId, FundingProgramEntity $fundingProgram): array {
    $action = FundingRecipientContactRelation::get()
      ->addWhere('funding_program_id', '=', $fundingProgram->getId());
    /** @phpstan-var array<int, contactRelationT> $contactRelations */
    $contactRelations = $this->api4->executeAction($action)->indexBy('id')->getArrayCopy();

    $relatedContacts = [];
    foreach ($contactRelations as $contactRelation) {
      $relatedContacts += $this->relatedContactsLoader->getRelatedContacts(
        $contactId,
        $contactRelation['type'],
        $contactRelation['properties'],
      );
    }

    return $relatedContacts;
  }

}
