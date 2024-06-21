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

use Civi\Api4\FundingDrawdown;
use Civi\Core\Event\GenericHookEvent;
use Civi\Funding\DocumentRender\CiviOffice\AbstractCiviOfficeTokenSubscriber;
use Civi\Funding\DocumentRender\CiviOffice\CiviOfficeContextDataHolder;
use Civi\Funding\DocumentRender\Token\TokenResolverInterface;
use Civi\Funding\Entity\AbstractEntity;
use Civi\Funding\Entity\DrawdownEntity;
use Civi\Funding\PayoutProcess\DrawdownManager;
use Civi\Funding\PayoutProcess\Token\DrawdownTokenNameExtractor;

/**
 * @phpstan-extends AbstractCiviOfficeTokenSubscriber<\Civi\Funding\Entity\DrawdownEntity>
 * @codeCoverageIgnore
 */
class DrawdownTokenSubscriber extends AbstractCiviOfficeTokenSubscriber {

  private DrawdownManager $drawdownManager;

  public static function getPriority(): int {
    return PayoutProcessTokenSubscriber::getPriority() + 1;
  }

  /**
   * @phpstan-param TokenResolverInterface<\Civi\Funding\Entity\DrawdownEntity> $tokenResolver
   */
  public function __construct(
    DrawdownManager $drawdownManager,
    CiviOfficeContextDataHolder $contextDataHolder,
    TokenResolverInterface $tokenResolver,
    DrawdownTokenNameExtractor $tokenNameExtractor
  ) {
    parent::__construct(
      $contextDataHolder,
      $tokenResolver,
      $tokenNameExtractor
    );
    $this->drawdownManager = $drawdownManager;
  }

  public function onCiviOfficeTokenContext(GenericHookEvent $event): void {
    parent::onCiviOfficeTokenContext($event);
    if ($this->getApiEntityName() === $event->entity_type || isset($event->context[$this->getContextKey() . 'Id'])) {
      /** @var \Civi\Funding\Entity\DrawdownEntity $drawdown */
      $drawdown = $event->context[$this->getContextKey()];
      $event->context['payoutProcessId'] ??= $drawdown->getPayoutProcessId();
    }
  }

  protected function getEntity(int $id): ?AbstractEntity {
    return $this->drawdownManager->get($id);
  }

  protected function getApiEntityName(): string {
    return FundingDrawdown::getEntityName();
  }

  protected function getEntityClass(): string {
    return DrawdownEntity::class;
  }

}
