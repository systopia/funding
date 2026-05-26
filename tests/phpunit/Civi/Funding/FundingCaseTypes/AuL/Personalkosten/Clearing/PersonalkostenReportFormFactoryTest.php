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

namespace Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Clearing;

use Civi\Funding\EntityFactory\ClearingProcessBundleFactory;
use Civi\Funding\Form\Traits\AssertFormTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Clearing\PersonalkostenReportFormFactory
 * @covers \Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Clearing\JsonSchema\PersonalkostenReportDataJsonSchema
 */
final class PersonalkostenReportFormFactoryTest extends TestCase {

  use AssertFormTrait;

  private PersonalkostenReportFormFactory $formFactory;

  protected function setUp(): void {
    parent::setUp();
    $this->formFactory = new PersonalkostenReportFormFactory();
  }

  public function testCreateReportForm(): void {
    $clearingProcessBundle = ClearingProcessBundleFactory::create();
    $form = $this->formFactory->createReportForm($clearingProcessBundle);

    static::assertScopesExist($form->getJsonSchema()->toStdClass(), $form->getUiSchema());
  }

}
