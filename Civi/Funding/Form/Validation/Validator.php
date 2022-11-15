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

namespace Civi\Funding\Form\Validation;

use Civi\RemoteTools\Form\JsonSchema\JsonSchema;
use Civi\RemoteTools\Util\JsonConverter;
use Opis\JsonSchema\Validator as OpisValidator;
use Systopia\JsonSchema\Errors\ErrorCollector;

final class Validator implements ValidatorInterface {

  private OpisValidator $validator;

  public function __construct(OpisValidator $validator) {
    $this->validator = $validator;
  }

  /**
   * @inheritDoc
   * @throws \JsonException
   */
  public function validate(JsonSchema $jsonSchema, array $data, int $maxErrors = 1): ValidationResult {
    $validationData = JsonConverter::toStdClass($data);
    $errorCollector = new ErrorCollector();
    $prevMaxErrors = $this->validator->getMaxErrors();
    try {
      $this->validator->setMaxErrors($maxErrors);
      $this->validator->validate($validationData, $jsonSchema->toStdClass(), ['errorCollector' => $errorCollector]);
    }
    finally {
      $this->validator->setMaxErrors($prevMaxErrors);
    }

    return new ValidationResult(JsonConverter::toArray($validationData), $errorCollector);
  }

}
