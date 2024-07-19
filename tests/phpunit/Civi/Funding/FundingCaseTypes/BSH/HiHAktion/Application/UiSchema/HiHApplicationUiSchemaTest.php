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

namespace Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Application\UiSchema;

use Civi\Funding\Form\Traits\AssertFormTrait;
use Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Application\JsonSchema\HiHApplicationJsonSchema;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingCaseTypes\BSH\HiHAktion\Application\UiSchema\HiHApplicationUiSchema
 */
final class HiHApplicationUiSchemaTest extends TestCase {

  use AssertFormTrait;

  public function testUiSchema(): void {
    $possibleRecipients = [
      1 => 'Organization 1',
      2 => 'Organization 2',
    ];
    $submitActions = [
      'submitAction1' => ['label' => 'Do Submit1', 'confirm' => NULL],
      'submitAction2' => ['label' => 'Do Submit2', 'confirm' => 'Proceed?'],
    ];

    $jsonSchema = new HiHApplicationJsonSchema(
      new \DateTime('2024-08-08'),
      new \DateTime('2024-08-09'),
      $possibleRecipients,
    );

    $uiSchema = new HiHApplicationUiSchema('EUR');
    static::assertScopesExist($jsonSchema->toStdClass(), $uiSchema);
  }

}
