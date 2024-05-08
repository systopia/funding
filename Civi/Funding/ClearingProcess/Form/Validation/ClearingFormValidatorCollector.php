<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

namespace Civi\Funding\ClearingProcess\Form\Validation;

use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Psr\Container\ContainerInterface;
use Systopia\JsonSchema\Tags\TaggedDataContainerInterface;

final class ClearingFormValidatorCollector implements ClearingFormValidatorInterface {

  private ContainerInterface $validators;

  /**
   * @param \Psr\Container\ContainerInterface $validators
   *   Validators with funding case type name as ID.
   */
  public function __construct(ContainerInterface $validators) {
    $this->validators = $validators;
  }

  /**
   * @inheritDoc
   */
  public function validate(
    ClearingProcessEntityBundle $clearingProcessBundle,
    array $data,
    TaggedDataContainerInterface $taggedData
  ): ClearingFormValidationResult {
    $fundingCaseTypeName = $clearingProcessBundle->getFundingCaseType()->getName();
    if ($this->validators->has($fundingCaseTypeName)) {
      /** @var \Civi\Funding\ClearingProcess\Form\Validation\ClearingFormValidatorInterface $validator */
      $validator = $this->validators->get($fundingCaseTypeName);

      return $validator->validate($clearingProcessBundle, $data, $taggedData);
    }

    return new ClearingFormValidationResult([], $data, $taggedData);
  }

}
