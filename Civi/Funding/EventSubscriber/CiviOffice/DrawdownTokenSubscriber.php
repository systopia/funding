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
use Civi\Funding\DocumentRender\CiviOffice\AbstractCiviOfficeTokenSubscriber;
use Civi\Funding\DocumentRender\CiviOffice\CiviOfficeContextDataHolder;
use Civi\Funding\Entity\AbstractEntity;
use Civi\Funding\Entity\DrawdownEntity;
use Civi\Funding\PayoutProcess\DrawdownManager;
use Civi\Funding\PayoutProcess\Token\DrawdownTokenNameExtractor;
use Civi\Funding\PayoutProcess\Token\DrawdownTokenResolver;

/**
 * @phpstan-extends AbstractCiviOfficeTokenSubscriber<\Civi\Funding\Entity\DrawdownEntity>
 * @codeCoverageIgnore
 */
class DrawdownTokenSubscriber extends AbstractCiviOfficeTokenSubscriber {

  private DrawdownManager $drawdownManager;

  public static function getPriority(): int {
    return PayoutProcessTokenSubscriber::getPriority() + 1;
  }

  public function __construct(
    DrawdownManager $drawdownManager,
    CiviOfficeContextDataHolder $contextDataHolder,
    DrawdownTokenResolver $tokenResolver,
    DrawdownTokenNameExtractor $tokenNameExtractor
  ) {
    parent::__construct(
      $contextDataHolder,
      $tokenResolver,
      $tokenNameExtractor
    );
    $this->drawdownManager = $drawdownManager;
  }

  protected function getApiEntityName(): string {
    return FundingDrawdown::getEntityName();
  }

  protected function getEntityClass(): string {
    return DrawdownEntity::class;
  }

  /**
   * @inheritDoc
   */
  protected function getRelatedContextSchemas(): array {
    return ['fundingPayoutProcessId'];
  }

  /**
   * @inheritDoc
   */
  protected function getRelatedContextValues(AbstractEntity $entity): array {
    return ['fundingPayoutProcessId' => $entity->getPayoutProcessId()];
  }

  protected function loadEntity(int $id): ?AbstractEntity {
    return $this->drawdownManager->get($id);
  }

}
