<?php
/*
 * Copyright (C) 2025 SYSTOPIA GmbH
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

use Civi\Api4\FundingClearingProcess;
use Civi\Funding\ClearingProcess\ClearingProcessManager;
use Civi\Funding\DocumentRender\CiviOffice\AbstractCiviOfficeTokenSubscriber;
use Civi\Funding\DocumentRender\CiviOffice\CiviOfficeContextDataHolder;
use Civi\Funding\DocumentRender\Token\TokenNameExtractorInterface;
use Civi\Funding\DocumentRender\Token\TokenResolverInterface;
use Civi\Funding\Entity\AbstractEntity;
use Civi\Funding\Entity\ClearingProcessEntity;

/**
 * @phpstan-extends AbstractCiviOfficeTokenSubscriber<\Civi\Funding\Entity\ClearingProcessEntity>
 * @codeCoverageIgnore
 */
class ClearingProcessTokenSubscriber extends AbstractCiviOfficeTokenSubscriber {

  private ClearingProcessManager $clearingProcessManager;

  public static function getPriority(): int {
    return ApplicationProcessTokenSubscriber::getPriority() + 1;
  }

  /**
   * @phpstan-param TokenResolverInterface<\Civi\Funding\Entity\ClearingProcessEntity> $tokenResolver
   */
  public function __construct(
    ClearingProcessManager $clearingProcessManager,
    CiviOfficeContextDataHolder $contextDataHolder,
    TokenResolverInterface $tokenResolver,
    TokenNameExtractorInterface $tokenNameExtractor
  ) {
    parent::__construct(
      $contextDataHolder,
      $tokenResolver,
      $tokenNameExtractor
    );
    $this->clearingProcessManager = $clearingProcessManager;
  }

  protected function getApiEntityName(): string {
    return FundingClearingProcess::getEntityName();
  }

  protected function getEntityClass(): string {
    return ClearingProcessEntity::class;
  }

  /**
   * @inheritDoc
   */
  protected function getRelatedContextSchemas(): array {
    return ['fundingApplicationProcessId'];
  }

  /**
   * @inheritDoc
   */
  protected function getRelatedContextValues(AbstractEntity $entity): array {
    return ['fundingApplicationProcessId' => $entity->getApplicationProcessId()];
  }

  protected function loadEntity(int $id): ?AbstractEntity {
    return $this->clearingProcessManager->get($id);
  }

}
