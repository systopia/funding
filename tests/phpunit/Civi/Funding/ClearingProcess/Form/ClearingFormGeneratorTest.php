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

namespace Civi\Funding\ClearingProcess\Form;

use Civi\Funding\ClearingProcess\ClearingActionsDeterminer;
use Civi\Funding\EntityFactory\ClearingProcessBundleFactory;
use Civi\Funding\Form\JsonFormsForm;
use Civi\Funding\Form\JsonFormsFormInterface;
use Civi\Funding\Translation\FormTranslatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ClearingProcess\Form\ClearingFormGenerator
 */
final class ClearingFormGeneratorTest extends TestCase {

  private ClearingActionsDeterminer&MockObject $actionsDeterminerMock;

  private MockObject&FormTranslatorInterface $formTranslatorMock;

  /**
   * @var \Civi\Funding\ClearingProcess\Form\ClearingFormGenerator
   */
  private ClearingFormGenerator $generator;

  private ReceiptsFormGeneratorInterface&MockObject $receiptsFormGeneratorMock;

  private ReportFormFactoryInterface&MockObject $reportDataFormFactoryMock;

  protected function setUp(): void {
    parent::setUp();
    $this->actionsDeterminerMock = $this->createMock(ClearingActionsDeterminer::class);
    $this->formTranslatorMock = $this->createMock(FormTranslatorInterface::class);
    $this->receiptsFormGeneratorMock = $this->createMock(ReceiptsFormGeneratorInterface::class);
    $this->reportDataFormFactoryMock = $this->createMock(ReportFormFactoryInterface::class);
    $this->generator = new ClearingFormGenerator(
      $this->actionsDeterminerMock,
      $this->formTranslatorMock,
      $this->receiptsFormGeneratorMock,
      $this->reportDataFormFactoryMock
    );
  }

  public function testEmpty(): void {
    $clearingProcessBundle = ClearingProcessBundleFactory::create();

    $this->receiptsFormGeneratorMock->expects(static::once())->method('generateReceiptsForm')
      ->with($clearingProcessBundle)
      ->willReturn(JsonFormsForm::newEmpty());
    $this->reportDataFormFactoryMock->expects(static::once())->method('createReportForm')
      ->with($clearingProcessBundle)
      ->willReturn(ReportForm::newEmpty());

    $this->actionsDeterminerMock->expects(static::once())->method('isActionAllowed')
      ->with('add-comment', $clearingProcessBundle)
      ->willReturn(FALSE);

    $this->actionsDeterminerMock->expects(static::once())->method('isEditAllowed')
      ->with($clearingProcessBundle)
      ->willReturn(FALSE);

    $this->formTranslatorMock->expects(static::once())->method('translateForm')
      ->with(
        static::isInstanceOf(JsonFormsFormInterface::class),
        $clearingProcessBundle->getFundingProgram(),
        $clearingProcessBundle->getFundingCaseType()
      )
      ->willReturnArgument(0);

    $form = $this->generator->generateForm($clearingProcessBundle);

    static::assertEquals(
      [
        'type' => 'object',
        'properties' => [
          'comment' => ['type' => 'null'],
          '_action' => [
            'type' => 'string',
            'enum' => [NULL],
          ],
        ],
        'required' => ['_action'],
        'additionalProperties' => FALSE,
      ],
      $form->getJsonSchema()->toArray()
    );

    static::assertEquals(
      [
        'type' => 'Group',
        'label' => 'Clearing',
        'elements' => [
          [
            'type' => 'Markup',
            'content' => '<p>There are no receipts necessary.</p>',
            'contentMediaType' => 'text/html',
          ],
        ],
        'options' => [
          'readonly' => TRUE,
        ],
      ],
      $form->getUiSchema()->toArray()
    );
  }

}
