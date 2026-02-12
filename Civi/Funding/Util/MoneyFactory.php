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

namespace Civi\Funding\Util;

use Brick\Math\RoundingMode;
use Brick\Money\Currency;
use Brick\Money\Exception\UnknownCurrencyException;
use Brick\Money\ISOCurrencyProvider;
use Brick\Money\Money;

final class MoneyFactory {

  /**
   * @param string|null $currencyCode
   *   Falls back to default currency configured in CiviCRM, if NULL.
   */
  public function createMoney(float $amount, ?string $currencyCode): Money {
    $currency = $this->getCurrencyObject($currencyCode ?? $this->getDefaultCurrencyCode());

    if (enum_exists(RoundingMode::class) && (new \ReflectionEnum(RoundingMode::class))->hasCase('HalfUp')) {
      // brick/math >=0.14.2 (Requires PHP 8.2)
      return Money::of($amount, $currency, NULL, RoundingMode::HalfUp);
    }
    else {
      // @phpstan-ignore classConstant.deprecated
      return Money::of($amount, $currency, NULL, RoundingMode::HALF_UP);
    }
  }

  /**
   * Copied from \CRM_Utils_Money::getCurrencyObject(), because it is marked as
   * internal.
   *
   * Get the currency object for a given currency code.
   *
   * Wrapper around the Brick library to support currency codes which Brick
   * doesn't support.
   */
  private function getCurrencyObject(string $currencyCode): Currency {
    try {
      $currency = ISOCurrencyProvider::getInstance()->getCurrency($currencyCode);
    }
    catch (UnknownCurrencyException $e) {
      $currency = new Currency(
        $currencyCode,
        0,
        $currencyCode,
        2
      );
    }

    return $currency;
  }

  /**
   * @codeCoverageIgnore Omit slow headless test.
   */
  private function getDefaultCurrencyCode(): string {
    // @phpstan-ignore-next-line
    return \Civi::settings()->get('defaultCurrency');
  }

}
