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

namespace Civi\Funding\ClearingProcess\Token;

use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\ClearingProcess\Form\ReportForm;
use Civi\Funding\ClearingProcess\Form\ReportFormFactoryInterface;
use Civi\Funding\DocumentRender\Token\TokenResolverInterface;
use Civi\Funding\DocumentRender\Token\TokenValueLoader;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\EntityFactory\ClearingProcessFactory;
use Civi\RemoteTools\JsonForms\JsonFormsElement;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;
use Civi\RemoteTools\JsonSchema\Util\JsonSchemaUtil;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ClearingProcess\Token\ClearingProcessTokenResolver
 */
final class ClearingProcessTokenResolverTest extends TestCase {

  private ApplicationProcessManager&MockObject $applicationProcessManagerMock;

  private ClearingProcessTokenResolver $clearingProcessTokenResolver;

  private ClearingCostReceiptsTableGenerator&MockObject $costReceiptsTableGeneratorMock;

  private ClearingCostsTableGenerator&MockObject $costsTableGeneratorMock;

  private ReportFormFactoryInterface&MockObject $reportFormFactoryMock;

  private ClearingResourcesReceiptsTableGenerator&MockObject $resourcesReceiptsTableGeneratorMock;

  private ClearingResourcesTableGenerator&MockObject $resourcesTableGeneratorMock;

  /**
   * @var \Civi\Funding\DocumentRender\Token\TokenResolverInterface<\Civi\Funding\Entity\ClearingProcessEntity>&MockObject
   */
  private TokenResolverInterface&MockObject $tokenResolverMock;

  private TokenValueLoader&MockObject $tokenValueLoaderMock;

  protected function setUp(): void {
    parent::setUp();

    $this->applicationProcessManagerMock = $this->createMock(ApplicationProcessManager::class);
    $this->costReceiptsTableGeneratorMock = $this->createMock(ClearingCostReceiptsTableGenerator::class);
    $this->costsTableGeneratorMock = $this->createMock(ClearingCostsTableGenerator::class);
    $this->resourcesReceiptsTableGeneratorMock = $this->createMock(ClearingResourcesReceiptsTableGenerator::class);
    $this->resourcesTableGeneratorMock = $this->createMock(ClearingResourcesTableGenerator::class);
    $this->reportFormFactoryMock = $this->createMock(ReportFormFactoryInterface::class);
    $this->tokenResolverMock = $this->createMock(TokenResolverInterface::class);
    $this->tokenValueLoaderMock = $this->createMock(TokenValueLoader::class);

    $this->clearingProcessTokenResolver = new ClearingProcessTokenResolver(
      $this->applicationProcessManagerMock,
      $this->costReceiptsTableGeneratorMock,
      $this->costsTableGeneratorMock,
      $this->resourcesReceiptsTableGeneratorMock,
      $this->resourcesTableGeneratorMock,
      $this->reportFormFactoryMock,
      $this->tokenResolverMock,
      $this->tokenValueLoaderMock,
    );
  }

  public function testLabel(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::create();
    $clearingProcess = ClearingProcessFactory::create();
    $clearingProcessBundle = new ClearingProcessEntityBundle($clearingProcess, $applicationProcessBundle);

    $this->applicationProcessManagerMock->method('getBundle')
      ->with($clearingProcess->getApplicationProcessId())
      ->willReturn($applicationProcessBundle);

    $this->tokenValueLoaderMock->method('getValue')
      ->with('FundingClearingProcess', $clearingProcess, 'report_data::foo')
      ->willReturn('baz');

    $this->reportFormFactoryMock->method('createReportForm')
      ->with($clearingProcessBundle)
      ->willReturn(new ReportForm(
        new JsonSchemaObject([
          'reportData' => new JsonSchemaObject([
            'foo' => new JsonSchemaString([
              'oneOf' => JsonSchemaUtil::buildTitledOneOf(['bar' => 'BAR', 'baz' => 'BAZ']),
            ]),
          ]),
        ]),
        new JsonFormsElement('test')
      ));

    $token = $this->clearingProcessTokenResolver->resolveToken(
      'FundingClearingProcess',
      $clearingProcess,
      'report_data::foo:label'
    );

    static::assertSame('BAZ', $token->value);
    static::assertSame('text/plain', $token->format);
  }

  public function testLabelMulti(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::create();
    $clearingProcess = ClearingProcessFactory::create();
    $clearingProcessBundle = new ClearingProcessEntityBundle($clearingProcess, $applicationProcessBundle);

    $this->applicationProcessManagerMock->method('getBundle')
      ->with($clearingProcess->getApplicationProcessId())
      ->willReturn($applicationProcessBundle);

    $this->tokenValueLoaderMock->method('getValue')
      ->with('FundingClearingProcess', $clearingProcess, 'report_data::foo')
      ->willReturn(['baz', 'bar', 'abc', TRUE, FALSE, 123]);

    $this->reportFormFactoryMock->method('createReportForm')
      ->with($clearingProcessBundle)
      ->willReturn(new ReportForm(
        new JsonSchemaObject([
          'reportData' => new JsonSchemaObject([
            'foo' => new JsonSchemaString([
              'oneOf' => JsonSchemaUtil::buildTitledOneOf(['bar' => 'BAR', 'baz' => 'BAZ']),
            ]),
          ]),
        ]),
        new JsonFormsElement('test')
      ));

    $token = $this->clearingProcessTokenResolver->resolveToken(
      'FundingClearingProcess',
      $clearingProcess,
      'report_data::foo:label'
    );

    static::assertSame(
      '    - ' . implode("<br />\n    - ", ['BAZ', 'BAR', 'abc', 'Yes', 'No', '123']) . "<br />\n",
      $token->value
    );
    static::assertSame('text/html', $token->format);
  }

}
