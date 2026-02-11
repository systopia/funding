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

namespace Civi\Funding\Validation;

/**
 * @phpstan-type validatorsT array<class-string, iterable<EntityValidatorInterface>>
 *
 * @codeCoverageIgnore
 *
 * @phpstan-ignore missingType.generics
 */
class EntityValidatorLoader {

  /**
   * @phpstan-var validatorsT
   *
   * @phpstan-ignore-next-line Generic argument of EntityValidatorInterface not defined.
   */
  private array $validators;

  /**
   * @phpstan-param validatorsT $validators
   *
   * @phpstan-ignore-next-line Generic argument of EntityValidatorInterface not defined.
   */
  public function __construct(array $validators) {
    $this->validators = $validators;
  }

  /**
   * @template E of \Civi\Funding\Entity\AbstractEntity
   *
   * @phpstan-param class-string<E> $class
   *
   * @phpstan-return iterable<EntityValidatorInterface<E>>
   */
  public function getValidators(string $class): iterable {
    return $this->validators[$class] ?? [];
  }

}
