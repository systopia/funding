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

use Civi\Api4\FundingCase;
use Civi\Core\CiviEventDispatcherInterface;
use Civi\Core\Event\GenericHookEvent;
use Civi\Funding\DocumentRender\CiviOffice\CiviOfficeContextDataHolder;
use Civi\Funding\DocumentRender\Token\ResolvedToken;
use Civi\Funding\Entity\FundingCaseEntity;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use Civi\Funding\FundingCase\FundingCaseManager;
use Civi\Funding\FundingCase\Token\FundingCaseTokenNameExtractor;
use Civi\Funding\FundingCase\Token\FundingCaseTokenResolver;
use Civi\Token\Event\TokenValueEvent;
use Civi\Token\TokenProcessor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\EventSubscriber\CiviOffice\FundingCaseTokenSubscriber
 * @covers \Civi\Funding\DocumentRender\CiviOffice\AbstractCiviOfficeTokenSubscriber
 */
final class FundingCaseTokenSubscriberTest extends TestCase {

  private CiviOfficeContextDataHolder $contextDataHolder;

  /**
   * @var \Civi\Funding\FundingCase\FundingCaseManager&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $fundingCaseManagerMock;

  /**
   * @var \Civi\Funding\FundingCase\Token\FundingCaseTokenResolver&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $tokenResolverMock;

  private FundingCaseTokenSubscriber $subscriber;

  protected function setUp(): void {
    parent::setUp();
    $this->fundingCaseManagerMock = $this->createMock(FundingCaseManager::class);
    $this->contextDataHolder = new CiviOfficeContextDataHolder();
    $this->tokenResolverMock = $this->createMock(FundingCaseTokenResolver::class);

    $tokenNameExtractorMock = $this->createMock(FundingCaseTokenNameExtractor::class);
    $tokenNameExtractorMock->method('getTokenNames')
      ->with(FundingCase::getEntityName(), FundingCaseEntity::class)
      ->willReturn([
        'my_field' => 'Label',
        'my_serialized' => 'Label 2',
        'my_serialized::' => 'With path',
      ]);

    $this->subscriber = new FundingCaseTokenSubscriber(
      $this->fundingCaseManagerMock,
      $this->contextDataHolder,
      $this->tokenResolverMock, $tokenNameExtractorMock,
    );
  }

  public function testGetSubscribedEvents(): void {
    // We do not test all subscriptions from \Civi\Token\AbstractTokenSubscriber.
    $expectedSubscriptions = [
      'civi.civioffice.tokenContext' => ['onCiviOfficeTokenContext', 1],
      'civi.token.eval' => ['evaluateTokens', 1],
    ];
    $subscriptions = $this->subscriber::getSubscribedEvents();

    foreach ($expectedSubscriptions as $eventName => [$method, $priority]) {
      static::assertSame([$method, $priority], $subscriptions[$eventName] ?? NULL);
      static::assertTrue(method_exists(get_class($this->subscriber), $method));
    }
  }

  public function testOnCiviOfficeTokenContext(): void {
    $context = [];
    $event = GenericHookEvent::create([
      'context' => &$context,
      'entity_type' => 'FundingCase',
      'entity_id' => FundingCaseFactory::DEFAULT_ID,
    ]);

    $fundingCase = FundingCaseFactory::createFundingCase();
    $this->fundingCaseManagerMock->method('get')
      ->with(FundingCaseFactory::DEFAULT_ID)
      ->willReturn($fundingCase);

    $this->subscriber->onCiviOfficeTokenContext($event);
    // @phpstan-ignore-next-line
    static::assertSame($fundingCase, $context['fundingCase']);
  }

  public function testOnCiviOfficeTokenContextWithContextValue(): void {
    $context = [];
    $event = GenericHookEvent::create([
      'context' => &$context,
      'entity_type' => 'EntityName',
      'entity_id' => 1,
    ]);

    $fundingCase = FundingCaseFactory::createFundingCase();
    $this->fundingCaseManagerMock->expects(static::never())->method('get');
    $this->contextDataHolder->addEntityData('EntityName', 1, ['fundingCase' => $fundingCase]);

    $this->subscriber->onCiviOfficeTokenContext($event);
    // @phpstan-ignore-next-line
    static::assertSame($fundingCase, $context['fundingCase']);
  }

  public function testCheckActive(): void {
    static::assertFalse($this->subscriber->checkActive(
      $this->createTokenProcessor([])
    ));
    static::assertTrue($this->subscriber->checkActive(
      $this->createTokenProcessor(['schema' => ['fundingCase']])
    ));
    static::assertTrue($this->subscriber->checkActive(
      $this->createTokenProcessor(['schema' => ['fundingCaseId']])
    ));

    $tokenProcessor = $this->createTokenProcessor([], ['fundingCase' => 'test']);
    static::assertTrue($this->subscriber->checkActive($tokenProcessor));
    static::assertEquals([
      'fundingCase',
      'fundingCaseTypeId',
      'fundingProgramId',
      'contactId',
    ], $tokenProcessor->context['schema']);

    $tokenProcessor = $this->createTokenProcessor([], ['fundingCaseId' => 'test']);
    static::assertTrue($this->subscriber->checkActive($tokenProcessor));
    static::assertEquals([
      'fundingCase',
      'fundingCaseTypeId',
      'fundingProgramId',
      'contactId',
    ], $tokenProcessor->context['schema']);
  }

  public function testEvaluate(): void {
    $tokenProcessor = $this->createTokenProcessor([]);
    $tokenProcessor->addMessage('test', '{funding_case.my_field}', 'text/plain');
    $tokenProcessor->addMessage('test2', '{funding_case.my_field2}', 'text/plain');

    $fundingCase = FundingCaseFactory::createFundingCase();
    $tokenProcessor->addRow(['fundingCase' => $fundingCase]);

    $this->tokenResolverMock->method('resolveToken')
      ->with(FundingCase::getEntityName(), $fundingCase, 'my_field')
      ->willReturn(new ResolvedToken('foo', 'text/html'));

    $event = new TokenValueEvent($tokenProcessor);
    static::assertSame(['my_field'], $this->subscriber->getActiveTokens($event));
    $row = $tokenProcessor->getRow(0);
    static::assertSame($fundingCase->getFundingCaseTypeId(), $row->context['fundingCaseTypeId']);
    static::assertSame($fundingCase->getFundingProgramId(), $row->context['fundingProgramId']);
    static::assertSame($fundingCase->getRecipientContactId(), $row->context['contactId']);

    $this->subscriber->evaluateTokens($event);
    $row = $tokenProcessor->getRow(0);
    static::assertSame([], $row->tokens);
    $row->format('text/html');
    // @phpstan-ignore-next-line
    static::assertSame('foo', $row->tokens['funding_case']['my_field'] ?? NULL);
  }

  public function testEvaluateWithId(): void {
    $fundingCase = FundingCaseFactory::createFundingCase(['recipient_contact_id' => 2]);
    $this->fundingCaseManagerMock->method('get')
      ->with(FundingCaseFactory::DEFAULT_ID)
      ->willReturn($fundingCase);

    $tokenProcessor = $this->createTokenProcessor([]);
    $tokenProcessor->addMessage('test', '{funding_case.my_field}', 'text/plain');
    $tokenProcessor->addMessage('test2', '{funding_case.my_field2}', 'text/plain');
    $tokenProcessor->addRow([
      'fundingCaseId' => FundingCaseFactory::DEFAULT_ID,
      'contactId' => 3,
    ]);

    $this->tokenResolverMock->method('resolveToken')
      ->with(FundingCase::getEntityName(), $fundingCase, 'my_field')
      ->willReturn(new ResolvedToken('foo', 'text/html'));

    $event = new TokenValueEvent($tokenProcessor);
    static::assertSame(['my_field'], $this->subscriber->getActiveTokens($event));
    $row = $tokenProcessor->getRow(0);
    static::assertSame($fundingCase, $row->context['fundingCase']);
    static::assertSame($fundingCase->getFundingCaseTypeId(), $row->context['fundingCaseTypeId']);
    static::assertSame($fundingCase->getFundingProgramId(), $row->context['fundingProgramId']);
    // Test 'contactId' is not overridden.
    static::assertSame(3, $row->context['contactId']);

    $this->subscriber->evaluateTokens($event);
    static::assertSame([], $row->tokens);
    $row->format('text/html');
    // @phpstan-ignore-next-line
    static::assertSame('foo', $row->tokens['funding_case']['my_field'] ?? NULL);
  }

  public function testEvaluateWithPath(): void {
    $tokenProcessor = $this->createTokenProcessor([]);
    $tokenProcessor->addMessage('test', '{funding_case.my_serialized::foo}', 'text/plain');

    $fundingCase = FundingCaseFactory::createFundingCase();
    $tokenProcessor->addRow(['fundingCase' => $fundingCase]);

    $this->tokenResolverMock->method('resolveToken')
      ->with(FundingCase::getEntityName(), $fundingCase, 'my_serialized::foo')
      ->willReturn(new ResolvedToken('bar', 'text/html'));

    $event = new TokenValueEvent($tokenProcessor);
    static::assertSame(['my_serialized::foo'], $this->subscriber->getActiveTokens($event));

    $this->subscriber->evaluateTokens($event);
    $row = $tokenProcessor->getRow(0);
    static::assertSame([], $row->tokens);
    $row->format('text/html');
    // @phpstan-ignore-next-line
    static::assertSame('bar', $row->tokens['funding_case']['my_serialized::foo'] ?? NULL);
  }

  /**
   * @phpstan-param array<string, mixed> $context
   * @phpstan-param array<string, mixed>|null $rowContext
   */
  private function createTokenProcessor(array $context, ?array $rowContext = NULL): TokenProcessor {
    $eventDispatcherMock = $this->createMock(CiviEventDispatcherInterface::class);
    // @phpstan-ignore-next-line
    $tokenProcessor = new TokenProcessor($eventDispatcherMock, $context);
    if (NULL !== $rowContext) {
      $tokenProcessor->addRow($rowContext);
    }

    return $tokenProcessor;
  }

}
