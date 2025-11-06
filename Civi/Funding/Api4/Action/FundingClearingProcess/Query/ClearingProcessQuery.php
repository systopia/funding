<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
 *
 * This program is free software: you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Civi\Funding\Api4\Action\FundingClearingProcess\Query;

final class ClearingProcessQuery {

  public static function recordedCostsSql(string $id, string $operator = '='): string {
    return "IFNULL(
      (SELECT SUM(item.amount) FROM civicrm_funding_clearing_cost_item item
        WHERE item.clearing_process_id $operator $id), 0)";
  }

  public static function recordedResourcesSql(string $id, string $operator = '='): string {
    return "IFNULL(
      (SELECT SUM(item.amount) FROM civicrm_funding_clearing_resources_item item
        WHERE item.clearing_process_id $operator $id), 0)";
  }

  public static function admittedCostsSql(string $id, string $operator = '='): string {
    return "IFNULL(
      (SELECT SUM(item.amount_admitted) FROM civicrm_funding_clearing_cost_item item
      WHERE item.clearing_process_id $operator $id), 0)";
  }

  public static function admittedResourcesSql(string $id, string $operator = '='): string {
    return "IFNULL(
      (SELECT SUM(item.amount_admitted) FROM civicrm_funding_clearing_resources_item item
      WHERE item.clearing_process_id $operator $id), 0)";
  }

  public static function amountCleared(string $id, string $operator = '='): string {
    return sprintf(
      '(SELECT %s - %s)',
      self::recordedCostsSql($id, $operator),
      self::recordedResourcesSql($id, $operator)
    );
  }

  public static function amountAdmitted(string $id, string $operator = '='): string {
    return sprintf(
      '(SELECT %s - %s)',
      self::admittedCostsSql($id, $operator),
      self::admittedResourcesSql($id, $operator)
    );
  }

}
