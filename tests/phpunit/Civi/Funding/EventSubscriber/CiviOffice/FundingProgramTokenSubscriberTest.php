<?php
/*
 * Copyright (C) 2023 SYSTOPIA GmbH
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

namespace Civi\Funding\EventSubscriber\CiviOffice;

use Civi\Api4\FundingProgram;
use Civi\Core\CiviEventDispatcherInterface;
use Civi\Core\Event\GenericHookEvent;
use Civi\Funding\DocumentRender\CiviOffice\CiviOfficeContextDataHolder;
use Civi\Funding\DocumentRender\Token\ResolvedToken;
use Civi\Funding\DocumentRender\Token\TokenNameExtractorInterface;
use Civi\Funding\DocumentRender\Token\TokenResolverInterface;
use Civi\Funding\Entity\FundingProgramEntity;
use Civi\Funding\EntityFactory\FundingProgramFactory;
use Civi\Funding\FundingProgram\FundingProgramManager;
use Civi\Token\Event\TokenValueEvent;
use Civi\Token\TokenProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\CiviOffice\FundingProgramTokenSubscriber
 * @covers \Civi\Funding\DocumentRender\CiviOffice\AbstractCiviOfficeTokenSubscriber
 */
final class FundingProgramTokenSubscriberTest extends TestCase {

  private CiviOfficeContextDataHolder $contextDataHolder;

  /**
   * @var \Civi\Funding\FundingProgram\FundingProgramManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fundingProgramManagerMock;

  /**
   * @var \Civi\Funding\DocumentRender\Token\TokenResolverInterface&\PHPUnit\Framework\MockObject\MockObject
   * @phpstan-ignore-next-line Generic argument of AbstractEntity not defined.
   */
  private MockObject $tokenResolverMock;

  private FundingProgramTokenSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->fundingProgramManagerMock = $this->createMock(FundingProgramManager::class);
    $this->contextDataHolder = new CiviOfficeContextDataHolder();
    $this->tokenResolverMock = $this->createMock(TokenResolverInterface::class);

    $tokenNameExtractorMock = $this->createMock(TokenNameExtractorInterface::class);
    $tokenNameExtractorMock->method('getTokenNames')
      ->with(FundingProgram::getEntityName(), FundingProgramEntity::class)
      ->willReturn([
        'my_field' => 'Label',
        'my_serialized' => 'Label 2',
        'my_serialized::' => 'With path',
      ]);

    $this->subscriber = new FundingProgramTokenSubscriber(
      $this->fundingProgramManagerMock,
      $this->contextDataHolder,
      $this->tokenResolverMock, $tokenNameExtractorMock,
    );
  }

  public function testGetSubscribedEvents(): void {
    // We do not test subscriptions from \Civi\Token\AbstractTokenSubscriber.
    $expectedSubscriptions = [
      'civi.civioffice.tokenContext' => 'onCiviOfficeTokenContext',
    ];
    $subscriptions = $this->subscriber::getSubscribedEvents();

    foreach ($expectedSubscriptions as $eventName => $method) {
      static::assertSame($method, $subscriptions[$eventName] ?? NULL);
      static::assertTrue(method_exists(get_class($this->subscriber), $method));
    }
  }

  public function testOnCiviOfficeTokenContext(): void {
    $context = [];
    $event = GenericHookEvent::create([
      'context' => &$context,
      'entity_type' => 'FundingProgram',
      'entity_id' => FundingProgramFactory::DEFAULT_ID,
    ]);

    $fundingProgram = FundingProgramFactory::createFundingProgram();
    $this->fundingProgramManagerMock->method('get')
      ->with(FundingProgramFactory::DEFAULT_ID)
      ->willReturn($fundingProgram);

    $this->subscriber->onCiviOfficeTokenContext($event);
    // @phpstan-ignore-next-line
    static::assertSame($fundingProgram, $context['fundingProgram']);
  }

  public function testOnCiviOfficeTokenContextWithContextValue(): void {
    $context = [];
    $event = GenericHookEvent::create([
      'context' => &$context,
      'entity_type' => 'EntityName',
      'entity_id' => 1,
    ]);

    $fundingProgram = FundingProgramFactory::createFundingProgram();
    $this->fundingProgramManagerMock->expects(static::never())->method('get');
    $this->contextDataHolder->addEntityData('EntityName', 1, ['fundingProgram' => $fundingProgram]);

    $this->subscriber->onCiviOfficeTokenContext($event);
    // @phpstan-ignore-next-line
    static::assertSame($fundingProgram, $context['fundingProgram']);
  }

  public function testBasic(): void {
    static::assertSame('funding_program', $this->subscriber->entity);
    static::assertSame(
      [
        'my_field' => 'Label',
        'my_serialized' => 'Label 2',
        'my_serialized::' => 'With path',
      ],
      $this->subscriber->tokenNames
    );
  }

  public function testCheckActive(): void {
    static::assertFalse($this->subscriber->checkActive(
      $this->createTokenProcessor([])
    ));
    static::assertTrue($this->subscriber->checkActive(
      $this->createTokenProcessor(['schema' => ['fundingProgram']])
    ));
    static::assertTrue($this->subscriber->checkActive(
      $this->createTokenProcessor([], ['fundingProgram' => 'test'])
    ));
  }

  public function testEvaluate(): void {
    $tokenProcessor = $this->createTokenProcessor([]);
    $tokenProcessor->addMessage('test', '{funding_program.my_field}', 'text/plain');
    $tokenProcessor->addMessage('test2', '{funding_program.my_field2}', 'text/plain');

    $fundingProgram = FundingProgramFactory::createFundingProgram();
    $tokenProcessor->addRow(['fundingProgram' => $fundingProgram]);

    $this->tokenResolverMock->method('resolveToken')
      ->with(FundingProgram::getEntityName(), $fundingProgram, 'my_field')
      ->willReturn(new ResolvedToken('foo', 'text/html'));

    $event = new TokenValueEvent($tokenProcessor);
    static::assertSame(['my_field'], $this->subscriber->getActiveTokens($event));

    $this->subscriber->evaluateTokens($event);
    $row = $tokenProcessor->getRow(0);
    static::assertSame([], $row->tokens);
    $row->format('text/html');
    // @phpstan-ignore-next-line
    static::assertSame('foo', $row->tokens['funding_program']['my_field'] ?? NULL);
  }

  public function testEvaluateWithPath(): void {
    $tokenProcessor = $this->createTokenProcessor([]);
    $tokenProcessor->addMessage('test', '{funding_program.my_serialized::foo}', 'text/plain');

    $fundingProgram = FundingProgramFactory::createFundingProgram();
    $tokenProcessor->addRow(['fundingProgram' => $fundingProgram]);

    $this->tokenResolverMock->method('resolveToken')
      ->with(FundingProgram::getEntityName(), $fundingProgram, 'my_serialized::foo')
      ->willReturn(new ResolvedToken('bar', 'text/html'));

    $event = new TokenValueEvent($tokenProcessor);
    static::assertSame(['my_serialized::foo'], $this->subscriber->getActiveTokens($event));

    $this->subscriber->evaluateTokens($event);
    $row = $tokenProcessor->getRow(0);
    static::assertSame([], $row->tokens);
    $row->format('text/html');
    // @phpstan-ignore-next-line
    static::assertSame('bar', $row->tokens['funding_program']['my_serialized::foo'] ?? NULL);
  }

  /**
   * @phpstan-param array<string, mixed> $context
   * @phpstan-param array<string, mixed>|null $rowContext
   */
  private function createTokenProcessor(array $context, array $rowContext = NULL): TokenProcessor {
    $eventDispatcherMock = $this->createMock(CiviEventDispatcherInterface::class);
    // @phpstan-ignore-next-line
    $tokenProcessor = new TokenProcessor($eventDispatcherMock, $context);
    if (NULL !== $rowContext) {
      $tokenProcessor->addRow($rowContext);
    }

    return $tokenProcessor;
  }

}
