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

namespace Civi\Funding\ApplicationProcess\Token;

use Civi\Funding\ApplicationProcess\ApplicationProcessManager;
use Civi\Funding\DocumentRender\Token\TokenResolverInterface;
use Civi\Funding\DocumentRender\Token\TokenValueLoader;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use Civi\Funding\Form\Application\ApplicationJsonSchemaFactoryInterface;
use Civi\RemoteTools\Api4\Api4Interface;
use Civi\RemoteTools\JsonSchema\JsonSchemaObject;
use Civi\RemoteTools\JsonSchema\JsonSchemaString;
use Civi\RemoteTools\JsonSchema\Util\JsonSchemaUtil;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\ApplicationProcess\Token\ApplicationProcessTokenResolver
 */
final class ApplicationProcessTokenResolverTest extends TestCase {

  private Api4Interface&MockObject $api4Mock;

  private ApplicationProcessManager&MockObject $applicationProcessManagerMock;

  private ApplicationProcessTokenResolver $applicationProcessTokenResolver;

  private ApplicationJsonSchemaFactoryInterface&MockObject $jsonSchemaFactoryMock;

  /**
   * @var \Civi\Funding\DocumentRender\Token\TokenResolverInterface<\Civi\Funding\Entity\ApplicationProcessEntity>&MockObject
   */
  private TokenResolverInterface&MockObject $tokenResolverMock;

  private TokenValueLoader&MockObject $tokenValueLoaderMock;

  protected function setUp(): void {
    parent::setUp();
    $this->api4Mock = $this->createMock(Api4Interface::class);
    $this->applicationProcessManagerMock = $this->createMock(ApplicationProcessManager::class);
    $this->jsonSchemaFactoryMock = $this->createMock(ApplicationJsonSchemaFactoryInterface::class);
    $this->tokenResolverMock = $this->createMock(TokenResolverInterface::class);
    $this->tokenValueLoaderMock = $this->createMock(TokenValueLoader::class);
    $this->applicationProcessTokenResolver = new ApplicationProcessTokenResolver(
      $this->api4Mock,
      $this->applicationProcessManagerMock,
      $this->jsonSchemaFactoryMock,
      $this->tokenResolverMock,
      $this->tokenValueLoaderMock
    );
  }

  public function testLabel(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::create();
    $applicationProcess = $applicationProcessBundle->getApplicationProcess();
    $this->applicationProcessManagerMock->method('getBundleForObject')
      ->with($applicationProcess)
      ->willReturn($applicationProcessBundle);
    $this->applicationProcessManagerMock->method('getStatusList')->with($applicationProcessBundle)->willReturn([]);

    $this->tokenValueLoaderMock->method('getValue')
      ->with('FundingApplicationProcess', $applicationProcess, 'request_data::foo')
      ->willReturn('baz');

    $this->jsonSchemaFactoryMock->method('createJsonSchemaExisting')
      ->with($applicationProcessBundle, [])
      ->willReturn(new JsonSchemaObject([
        'foo' => new JsonSchemaString([
          'oneOf' => JsonSchemaUtil::buildTitledOneOf(['bar' => 'BAR', 'baz' => 'BAZ']),
        ]),
      ]));

    $token = $this->applicationProcessTokenResolver->resolveToken(
      'FundingApplicationProcess',
      $applicationProcess,
      'request_data::foo:label'
    );

    static::assertSame('BAZ', $token->value);
    static::assertSame('text/plain', $token->format);
  }

  public function testLabelMulti(): void {
    $applicationProcessBundle = ApplicationProcessBundleFactory::create();
    $applicationProcess = $applicationProcessBundle->getApplicationProcess();
    $this->applicationProcessManagerMock->method('getBundleForObject')
      ->with($applicationProcess)
      ->willReturn($applicationProcessBundle);
    $this->applicationProcessManagerMock->method('getStatusList')->with($applicationProcessBundle)->willReturn([]);

    $this->tokenValueLoaderMock->method('getValue')
      ->with('FundingApplicationProcess', $applicationProcess, 'request_data::foo')
      ->willReturn(['baz', 'bar', 'abc', TRUE, FALSE, 123]);

    $this->jsonSchemaFactoryMock->method('createJsonSchemaExisting')
      ->with($applicationProcessBundle, [])
      ->willReturn(new JsonSchemaObject([
        'foo' => new JsonSchemaString([
          'oneOf' => JsonSchemaUtil::buildTitledOneOf(['bar' => 'BAR', 'baz' => 'BAZ']),
        ]),
      ]));

    $token = $this->applicationProcessTokenResolver->resolveToken(
      'FundingApplicationProcess',
      $applicationProcess,
      'request_data::foo:label'
    );

    static::assertSame(
      '    - ' . implode("<br />\n    - ", ['BAZ', 'BAR', 'abc', 'Yes', 'No', '123']) . "<br />\n",
      $token->value
    );
    static::assertSame('text/html', $token->format);
  }

}
