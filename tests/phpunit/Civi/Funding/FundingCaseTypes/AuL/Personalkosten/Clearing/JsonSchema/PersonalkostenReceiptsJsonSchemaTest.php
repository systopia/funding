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

namespace Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Clearing\JsonSchema;

use Civi\Funding\ClearingProcess\ClearingProcessPermissions;
use Civi\Funding\ClearingProcess\JsonSchema\Validator\ClearingSchemaValidator;
use Civi\Funding\ClearingProcess\JsonSchema\Validator\OpisClearingValidatorFactory;
use Civi\Funding\EntityFactory\ApplicationCostItemFactory;
use Civi\Funding\EntityFactory\ClearingProcessBundleFactory;
use Civi\Funding\Form\Traits\AssertFormTrait;
use Civi\RemoteTools\Util\JsonConverter;
use PHPUnit\Framework\TestCase;
use Systopia\JsonSchema\Translation\NullTranslator;

/**
 * @covers \Civi\Funding\FundingCaseTypes\AuL\Personalkosten\Clearing\JsonSchema\PersonalkostenReceiptsJsonSchema
 */
final class PersonalkostenReceiptsJsonSchemaTest extends TestCase {

  use AssertFormTrait;

  private ClearingSchemaValidator $validator;

  protected function setUp(): void {
    parent::setUp();
    $this->validator = new ClearingSchemaValidator(
      new NullTranslator(),
      OpisClearingValidatorFactory::getValidator()
    );
  }

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

    $data = [
      'costItems' => [
        'personalkosten' => [
          'records' => [
            'personalkosten' => [
              'amount' => 1234.56,
            ],
          ],
        ],
        'sachkostenpauschale' => [
          'records' => [
            'sachkostenpauschale' => [
              'amount' => 222.22,
            ],
          ],
        ],
      ],
    ];
    $result = $this->validator->validate($jsonSchema, $data);
    static::assertSame([], $result->getLeafErrorMessages());

    $resultData = $result->getData();
    static::assertIsArray($resultData['costItems']['personalkosten']['records']['personalkosten']);
    static::assertArrayNotHasKey(
      'amountAdmitted',
      $resultData['costItems']['personalkosten']['records']['personalkosten']
    );
  }

  public function testSachkostenPauschaleMustNotBeChanged(): void {
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

    $data = [
      'costItems' => [
        'personalkosten' => [
          'records' => [
            'personalkosten' => [
              'amount' => 1234.56,
            ],
          ],
        ],
        'sachkostenpauschale' => [
          'records' => [
            'sachkostenpauschale' => [
              'amount' => 222.23,
            ],
          ],
        ],
      ],
    ];
    $result = $this->validator->validate($jsonSchema, $data);
    static::assertNotEmpty(
      $result->getLeafErrorMessages()['/costItems/sachkostenpauschale/records/sachkostenpauschale/amount']
    );
  }

  public function testPersonalkostenAmountAdmittedCalculated(): void {
    $personalkostenBeantragt = ApplicationCostItemFactory::createApplicationCostItem(['amount' => 1111.11]);
    $sachkostenpauschale = ApplicationCostItemFactory::createApplicationCostItem(['amount' => 222.22]);
    $foerderquote = 12;
    $clearingProcessBundle = ClearingProcessBundleFactory::create(
      fundingCaseValues: [
        'permissions' => [ClearingProcessPermissions::REVIEW_CALCULATIVE],
      ],
      fundingProgramValues: [
        'funding_program_extra.foerderquote' => $foerderquote,
      ],
    );

    $jsonSchema = new PersonalkostenReceiptsJsonSchema(
      $personalkostenBeantragt,
      $sachkostenpauschale,
      $clearingProcessBundle
    );

    $data = [
      'costItems' => [
        'personalkosten' => [
          'records' => [
            'personalkosten' => [
              '_id' => $personalkostenBeantragt->getId(),
              'amount' => 1234.56,
            ],
          ],
        ],
        'sachkostenpauschale' => [
          'records' => [
            'sachkostenpauschale' => [
              '_id' => $sachkostenpauschale->getId(),
              'amount' => 222.22,
              'amountAdmitted' => 222.22,
            ],
          ],
        ],
      ],
    ];
    $result = $this->validator->validate($jsonSchema, $data);
    static::assertSame([], $result->getLeafErrorMessages());

    static::assertSame(
      round($foerderquote * 1234.56 / 100, 2),
      $result->getData()['costItems']['personalkosten']['records']['personalkosten']['amountAdmitted']
    );

    $resultData = JsonConverter::toStdClass($result->getData());
    static::assertAllPropertiesSet($jsonSchema->toStdClass(), $resultData);
  }

}
