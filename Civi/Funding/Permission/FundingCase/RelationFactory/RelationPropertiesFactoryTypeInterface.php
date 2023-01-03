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

namespace Civi\Funding\Permission\FundingCase\RelationFactory;

interface RelationPropertiesFactoryTypeInterface {

  public static function getName(): string;

  public function getLabel(): string;

  public function getTemplate(): string;

  public function getHelp(): string;

  /**
   * @phpstan-return array<string, mixed>
   *   JSON serializable.
   */
  public function getExtra(): array;

  /**
   * @phpstan-return array{
   *   name: string,
   *   label: string,
   *   template: string,
   *   help: string,
   *   extra: array<string, mixed>,
   * }
   */
  public function toArray(): array;

}
