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

namespace Civi\Funding\ClearingProcess\JsonSchema\Validator;

use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\Validation\ValidationResultInterface;
use Civi\RemoteTools\JsonSchema\Validation\Validator;
use Civi\RemoteTools\JsonSchema\Validation\ValidatorInterface;
use Systopia\JsonSchema\Translation\TranslatorInterface;

final class ClearingSchemaValidator implements ValidatorInterface {

  private Validator $validator;

  public function __construct(TranslatorInterface $translator, OpisClearingValidator $validator) {
    $this->validator = new Validator($translator, $validator);
  }

  /**
   * @inheritDoc
   * @throws \JsonException
   */
  public function validate(JsonSchema $jsonSchema, array $data, int $maxErrors = 1): ValidationResultInterface {
    return $this->validator->validate($jsonSchema, $data, $maxErrors);
  }

}
