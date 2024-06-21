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

namespace Civi\Funding\PayoutProcess\Token;

use Civi\Funding\DocumentRender\Token\TokenNameExtractorInterface;
use CRM_Funding_ExtensionUtil as E;

/**
 * @codeCoverageIgnore
 */
final class DrawdownTokenNameExtractor implements TokenNameExtractorInterface {

  private TokenNameExtractorInterface $tokenNameExtractor;

  public function __construct(TokenNameExtractorInterface $tokenNameExtractor) {
    $this->tokenNameExtractor = $tokenNameExtractor;
  }

  /**
   * @inheritDoc
   */
  public function getTokenNames(string $entityName, string $entityClass): array {
    return $this->tokenNameExtractor->getTokenNames($entityName, $entityClass)
      + ['reviewer_contact_display_name' => E::ts('Display name of drawdown review contact')];
  }

}
