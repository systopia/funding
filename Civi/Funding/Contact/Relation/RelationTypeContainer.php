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

namespace Civi\Funding\Contact\Relation;

/**
 * @codeCoverageIgnore
 */
final class RelationTypeContainer implements RelationTypeContainerInterface {

  /**
   * @phpstan-var iterable<\Civi\Funding\Contact\Relation\RelationTypeInterface>
   */
  private iterable $relationTypes;

  /**
   * @phpstan-param iterable<\Civi\Funding\Contact\Relation\RelationTypeInterface> $relationTypes
   */
  public function __construct(iterable $relationTypes) {
    $this->relationTypes = $relationTypes;
  }

  /**
   * @inheritDoc
   */
  public function getRelationTypes(): array {
    return [...$this->relationTypes];
  }

}
