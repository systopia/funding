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

use Civi\Api4\FundingPayoutProcess;
use Civi\Funding\DocumentRender\CiviOffice\AbstractCiviOfficeTokenSubscriber;
use Civi\Funding\DocumentRender\CiviOffice\CiviOfficeContextDataHolder;
use Civi\Funding\DocumentRender\Token\TokenNameExtractorInterface;
use Civi\Funding\DocumentRender\Token\TokenResolverInterface;
use Civi\Funding\Entity\AbstractEntity;
use Civi\Funding\Entity\PayoutProcessEntity;
use Civi\Funding\PayoutProcess\PayoutProcessManager;

/**
 * @phpstan-extends AbstractCiviOfficeTokenSubscriber<\Civi\Funding\Entity\PayoutProcessEntity>
 * @codeCoverageIgnore
 */
class PayoutProcessTokenSubscriber extends AbstractCiviOfficeTokenSubscriber {

  private PayoutProcessManager $payoutProcessManager;

  /**
   * @phpstan-param TokenResolverInterface<\Civi\Funding\Entity\PayoutProcessEntity> $tokenResolver
   */
  public function __construct(
    PayoutProcessManager $payoutProcessManager,
    CiviOfficeContextDataHolder $contextDataHolder,
    TokenResolverInterface $tokenResolver,
    TokenNameExtractorInterface $tokenNameExtractor
  ) {
    parent::__construct(
      $contextDataHolder,
      $tokenResolver,
      $tokenNameExtractor
    );
    $this->payoutProcessManager = $payoutProcessManager;
  }

  protected function getEntity(int $id): ?AbstractEntity {
    return $this->payoutProcessManager->get($id);
  }

  protected function getApiEntityName(): string {
    return FundingPayoutProcess::getEntityName();
  }

  protected function getEntityClass(): string {
    return PayoutProcessEntity::class;
  }

}
