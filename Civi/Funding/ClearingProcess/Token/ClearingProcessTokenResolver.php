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
use Civi\Funding\DocumentRender\Token\ResolvedToken;
use Civi\Funding\DocumentRender\Token\TokenResolverInterface;
use Civi\Funding\Entity\AbstractEntity;
use Civi\Funding\Entity\ClearingProcessEntity;
use Civi\Funding\Entity\ClearingProcessEntityBundle;

/**
 * @implements TokenResolverInterface<ClearingProcessEntity>
 */
final class ClearingProcessTokenResolver implements TokenResolverInterface {

  /**
   * @var array<int, ClearingProcessEntityBundle>
   */
  private array $clearingProcessBundles = [];

  /**
   * @param \Civi\Funding\DocumentRender\Token\TokenResolverInterface<ClearingProcessEntity> $tokenResolver
   */
  public function __construct(
    private readonly ApplicationProcessManager $applicationProcessManager,
    private readonly ClearingCostReceiptsTableGenerator $costReceiptTableGenerator,
    private readonly ClearingCostsTableGenerator $costsTableGenerator,
    private readonly ClearingResourcesReceiptsTableGenerator $resourcesReceiptTableGenerator,
    private readonly ClearingResourcesTableGenerator $resourcesTableGenerator,
    /**
     * @var \Civi\Funding\DocumentRender\Token\TokenResolverInterface<ClearingProcessEntity>
     */
    private readonly TokenResolverInterface $tokenResolver
  ) {}

  /**
   * @inheritDoc
   *
   * @throws \CRM_Core_Exception
   */
  public function resolveToken(string $entityName, AbstractEntity $entity, string $tokenName): ResolvedToken {
    if ('costs_table' === $tokenName) {
      return new ResolvedToken(
        $this->costsTableGenerator->generateTable($this->getClearingProcessBundle($entity)),
        'text/html'
      );
    }

    if ('resources_table' === $tokenName) {
      return new ResolvedToken(
        $this->resourcesTableGenerator->generateTable($this->getClearingProcessBundle($entity)),
        'text/html'
      );
    }

    if ('cost_receipts_table' === $tokenName) {
      return new ResolvedToken(
        $this->costReceiptTableGenerator->generateReceiptsTable($this->getClearingProcessBundle($entity)),
        'text/html'
      );
    }

    if ('resources_receipts_table' === $tokenName) {
      return new ResolvedToken(
        $this->resourcesReceiptTableGenerator->generateReceiptsTable($this->getClearingProcessBundle($entity)),
        'text/html'
      );
    }

    return $this->tokenResolver->resolveToken($entityName, $entity, $tokenName);
  }

  /**
   * @throws \CRM_Core_Exception
   */
  private function getClearingProcessBundle(ClearingProcessEntity $clearingProcess): ClearingProcessEntityBundle {
    if (isset($this->clearingProcessBundles[$clearingProcess->getId()])) {
      return $this->clearingProcessBundles[$clearingProcess->getId()];
    }

    $applicationProcessBundle = $this->applicationProcessManager
      ->getBundle($clearingProcess->getApplicationProcessId());
    assert(NULL !== $applicationProcessBundle);

    return $this->clearingProcessBundles[$clearingProcess->getId()]
      = new ClearingProcessEntityBundle($clearingProcess, $applicationProcessBundle);
  }

}
