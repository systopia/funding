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

namespace Civi\Funding\Permission\FundingCase\RelationFactory\Factory;

use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\Permission\ContactRelation\Types\ContactRelationships;
use Civi\Funding\Permission\FundingCase\RelationFactory\RelationPropertiesFactoryInterface;
use Civi\Funding\Permission\FundingCase\RelationFactory\Types\CreationAndRecipientContactRelationship;
use Webmozart\Assert\Assert;

/**
 * @phpstan-import-type propertiesT from ContactRelationships
 *
 * @codeCoverageIgnore
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
final class CreationAndRecipientContactRelationshipRelationPropertiesFactory implements RelationPropertiesFactoryInterface {
// phpcs:enable
  public static function getSupportedFactoryType(): string {
    return CreationAndRecipientContactRelationship::NAME;
  }

  /**
   * @phpstan-return propertiesT
   */
  public function createRelationProperties(
    array $properties,
    FundingCaseEntity $fundingCase
  ): array {
    Assert::integerish($properties['creationContactRelationshipTypeId']);
    Assert::integerish($properties['recipientContactRelationshipTypeId']);

    return [
      'relationships' => [
        [
          'contactId' => $fundingCase->getCreationContactId(),
          'relationshipTypeId' => (int) $properties['creationContactRelationshipTypeId'],
        ],
        [
          'contactId' => $fundingCase->getRecipientContactId(),
          'relationshipTypeId' => (int) $properties['recipientContactRelationshipTypeId'],
        ],
      ],
    ];
  }

  public function getRelationType(): string {
    return ContactRelationships::NAME;
  }

}
