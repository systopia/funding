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

namespace Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Clearing\UiSchema;

use Civi\Funding\EntityFactory\ApplicationCostItemFactory;
use Civi\Funding\EntityFactory\ClearingProcessBundleFactory;
use Civi\Funding\Form\Traits\AssertFormTrait;
use Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Clearing\JsonSchema\PersonalkostenReceiptsJsonSchema;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Clearing\UiSchema\PersonalkostenReceiptsUiSchema
 */
final class PersonalkostenReceiptsUiSchemaTest extends TestCase {

  use AssertFormTrait;

  public function test(): void {
    $personalkostenBeantragt = ApplicationCostItemFactory::createApplicationCostItem(['amount' => 1111.11]);
    $sachkostenpauschale = ApplicationCostItemFactory::createApplicationCostItem(['amount' => 222.22]);
    $clearingProcessBundle = ClearingProcessBundleFactory::create(
      fundingProgramValues: [
        'funding_program_extra.foerderquote' => 12,
      ],
    );

    $jsonSchema = new PersonalkostenReceiptsJsonSchema(
      $personalkostenBeantragt,
      $sachkostenpauschale,
      $clearingProcessBundle
    );

    $uiSchema = new PersonalkostenReceiptsUiSchema($clearingProcessBundle, '1111,11 €');
    static::assertScopesExist($jsonSchema->toStdClass(), $uiSchema);
  }

}
