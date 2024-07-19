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

namespace Civi\Funding\IJB\Application\UiSchema;

use Civi\Funding\Form\Traits\AssertFormTrait;
use Civi\Funding\IJB\Application\JsonSchema\IJBApplicationJsonSchema;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\IJB\Application\UiSchema\IJBApplicationUiSchema
 */
final class IJBApplicationUiSchemaTest extends TestCase {

  use AssertFormTrait;

  public function testUiSchema(): void {
    $possibleRecipients = [
      1 => 'Organization 1',
      2 => 'Organization 2',
    ];

    $jsonSchema = new IJBApplicationJsonSchema(
      new \DateTime('2022-08-24'),
      new \DateTime('2022-08-25'),
      $possibleRecipients,
    );

    $uiSchema = new IJBApplicationUiSchema('EUR');
    static::assertScopesExist($jsonSchema->toStdClass(), $uiSchema);
  }

}
