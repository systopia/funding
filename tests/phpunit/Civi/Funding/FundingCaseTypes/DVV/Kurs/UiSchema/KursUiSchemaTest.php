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

namespace Civi\Funding\FundingCaseTypes\DVV\Kurs\UiSchema;

use Civi\Funding\Form\Traits\AssertFormTrait;
use Civi\Funding\FundingCaseTypes\DVV\Kurs\Application\JsonSchema\KursJsonSchema;
use Civi\Funding\FundingCaseTypes\DVV\Kurs\Application\UISchema\KursUiSchema;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingCaseTypes\DVV\Kurs\Application\UISchema\KursUiSchema
 */
class KursUiSchemaTest extends TestCase {

  use AssertFormTrait;

  public function testUiSchema(): void {
    $possibleRecipients = [
      1 => 'Organization 1',
      2 => 'Organization 2',
    ];

    $jsonSchema = new KursJsonSchema(
      new \DateTime('2022-08-24'),
      new \DateTime('2022-08-25'),
      $possibleRecipients,
      1.0,
      2.0,
      3.0
    );

    $uiSchema = new KursUiSchema('€', KursUiSchema::FLAG_SHOW_RECIPIENTS_CONTROL);
    static::assertScopesExist($jsonSchema->toStdClass(), $uiSchema);
  }

}
