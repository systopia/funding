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

use Civi\Funding\Entity\AbstractEntity;

/**
 * @implements EntityValidatorInterface<AbstractEntity>
 */
final class EntityValidator implements EntityValidatorInterface {

  private EntityValidatorLoader $entityValidatorLoader;

  public function __construct(EntityValidatorLoader $entityValidatorLoader) {
    $this->entityValidatorLoader = $entityValidatorLoader;
  }

  /**
   * @inheritDoc
   *
   * @phpstan-ignore-next-line Generic argument of AbstractEntity not defined.
   */
  public function validate(
    AbstractEntity $new,
    AbstractEntity $current,
    bool $checkPermissions
  ): EntityValidationResult {
    $result = new EntityValidationResult();
    foreach ($this->entityValidatorLoader->getValidators(get_class($new)) as $validator) {
      $result->merge($validator->validate($new, $current, $checkPermissions));
    }

    return $result;
  }

  /**
   * @inheritDoc
   *
   * @phpstan-ignore-next-line Generic argument of AbstractEntity not defined.
   */
  public function validateNew(AbstractEntity $new, bool $checkPermissions): EntityValidationResult {
    $result = new EntityValidationResult();
    foreach ($this->entityValidatorLoader->getValidators(get_class($new)) as $validator) {
      $result->merge($validator->validateNew($new, $checkPermissions));
    }

    return $result;
  }

}
