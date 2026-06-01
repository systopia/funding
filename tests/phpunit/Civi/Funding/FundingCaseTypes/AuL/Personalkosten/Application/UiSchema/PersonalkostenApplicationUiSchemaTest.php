<?php
/*
 * Copyright (C) 2026 SYSTOPIA GmbH
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

namespace Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Application\UiSchema;

use Civi\Funding\Form\Traits\AssertFormTrait;
use Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Application\JsonSchema\PersonalkostenApplicationJsonSchema;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Application\UiSchema\PersonalkostenApplicationUiSchema
 */
final class PersonalkostenApplicationUiSchemaTest extends TestCase {

  use AssertFormTrait;

  public function testUiSchema(): void {
    $jsonSchema = new PersonalkostenApplicationJsonSchema(
      10,
      100.1,
      new \DateTime('2026-05-04'),
      new \DateTime('2026-05-05'),
      [],
      []
    );

    $uiSchema = new PersonalkostenApplicationUiSchema('EUR');
    static::assertScopesExist($jsonSchema->toStdClass(), $uiSchema);
  }

}
