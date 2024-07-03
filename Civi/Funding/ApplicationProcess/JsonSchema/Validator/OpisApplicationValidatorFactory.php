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

namespace Civi\Funding\ApplicationProcess\JsonSchema\Validator;

use Systopia\ExpressionLanguage\SystopiaExpressionLanguage;
use Systopia\JsonSchema\Expression\SymfonyExpressionHandler;

/**
 * @codeCoverageIgnore
 */
final class OpisApplicationValidatorFactory {

  private static OpisApplicationValidator $validator;

  public static function getValidator(): OpisApplicationValidator {
    if (!isset(self::$validator)) {
      $expressionHandler = new SymfonyExpressionHandler(new SystopiaExpressionLanguage());
      self::$validator = new OpisApplicationValidator([
        'convertEmptyArrays' => TRUE,
        'calculator' => $expressionHandler,
        'evaluator' => $expressionHandler,
      ]);
    }

    return self::$validator;
  }

}
