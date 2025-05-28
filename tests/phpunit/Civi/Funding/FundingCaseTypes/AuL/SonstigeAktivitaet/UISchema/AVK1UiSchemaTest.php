<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

namespace Civi\Funding\FundingCaseTypes\AuL\SonstigeAktivitaet\UISchema;

use Civi\Funding\Form\Traits\AssertFormTrait;
use Civi\Funding\FundingCaseTypes\AuL\SonstigeAktivitaet\Application\JsonSchema\AVK1JsonSchema;
use Civi\Funding\FundingCaseTypes\AuL\SonstigeAktivitaet\Application\UISchema\AVK1UiSchema;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingCaseTypes\AuL\SonstigeAktivitaet\Application\UISchema\AVK1UiSchema
 */
class AVK1UiSchemaTest extends TestCase {

  use AssertFormTrait;

  public function testUiSchema(): void {
    $possibleRecipients = [
      1 => 'Organization 1',
      2 => 'Organization 2',
    ];

    $jsonSchema = new AVK1JsonSchema(
      new \DateTime('2022-08-24'),
      new \DateTime('2022-08-25'),
      $possibleRecipients,
    );

    $uiSchema = new AVK1UiSchema('€');
    static::assertScopesExist($jsonSchema->toStdClass(), $uiSchema);
  }

}
