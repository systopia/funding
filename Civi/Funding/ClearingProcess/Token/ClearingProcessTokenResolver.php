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
use Civi\Funding\ClearingProcess\Form\ReportFormFactoryInterface;
use Civi\Funding\DocumentRender\Token\ResolvedToken;
use Civi\Funding\DocumentRender\Token\TokenResolverInterface;
use Civi\Funding\DocumentRender\Token\TokenValueLoader;
use Civi\Funding\DocumentRender\Token\ValueConverter;
use Civi\Funding\Entity\AbstractEntity;
use Civi\Funding\Entity\ClearingProcessEntity;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\JsonSchema\Util\JsonSchemaValueUtil;
use Civi\RemoteTools\JsonSchema\JsonSchema;
use Civi\RemoteTools\JsonSchema\Util\JsonSchemaUtil;
use Webmozart\Assert\Assert;

/**
 * @implements TokenResolverInterface<ClearingProcessEntity>
 */
final class ClearingProcessTokenResolver implements TokenResolverInterface {

  /**
   * @var array<int, ClearingProcessEntityBundle>
   */
  private array $clearingProcessBundles = [];

  /**
   * @var array<int, JsonSchema>
   */
  private $reportJsonSchemas = [];

  /**
   * @param \Civi\Funding\DocumentRender\Token\TokenResolverInterface<ClearingProcessEntity> $tokenResolver
   */
  public function __construct(
    private readonly ApplicationProcessManager $applicationProcessManager,
    private readonly ClearingCostReceiptsTableGenerator $costReceiptsTableGenerator,
    private readonly ClearingCostsTableGenerator $costsTableGenerator,
    private readonly ClearingResourcesReceiptsTableGenerator $resourcesReceiptsTableGenerator,
    private readonly ClearingResourcesTableGenerator $resourcesTableGenerator,
    private readonly ReportFormFactoryInterface $reportFormFactory,
    /**
     * @var \Civi\Funding\DocumentRender\Token\TokenResolverInterface<ClearingProcessEntity>
     */
    private readonly TokenResolverInterface $tokenResolver,
    private readonly TokenValueLoader $tokenValueLoader,
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
        $this->costReceiptsTableGenerator->generateReceiptsTable($this->getClearingProcessBundle($entity)),
        'text/html'
      );
    }

    if ('resources_receipts_table' === $tokenName) {
      return new ResolvedToken(
        $this->resourcesReceiptsTableGenerator->generateReceiptsTable($this->getClearingProcessBundle($entity)),
        'text/html'
      );
    }

    if (1 === preg_match('/^report_data::.+:label$/', $tokenName)) {
      return $this->resolveTokenLabel($entityName, $entity, $tokenName);
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

  /**
   * @throws \CRM_Core_Exception
   */
  private function resolveTokenLabel(
    string $entityName,
    ClearingProcessEntity $clearingProcess,
    string $tokenName
  ): ResolvedToken {
    $tokenName = substr($tokenName, 0, -6);
    [$fieldName, $path] = explode('::', $tokenName, 2);
    $value = $this->tokenValueLoader->getValue($entityName, $clearingProcess, $tokenName);

    $jsonSchema = $this->getJsonSchema($clearingProcess);
    $propertyPath = array_merge(['reportData'], explode('.', $path));
    $propertySchema = JsonSchemaUtil::getPropertySchemaAt($jsonSchema, $propertyPath);

    if (NULL === $propertySchema) {
      return ValueConverter::toResolvedToken($value);
    }

    if (is_array($value)) {
      Assert::allNullOrScalar($value);

      return ValueConverter::toResolvedToken(
        array_map(fn ($v) => JsonSchemaValueUtil::valueToString($v, $propertySchema), $value)
      );
    }

    Assert::nullOrScalar($value);

    return ValueConverter::toResolvedToken(JsonSchemaValueUtil::valueToString($value, $propertySchema));
  }

  private function getJsonSchema(ClearingProcessEntity $clearingProcess): JsonSchema {
    return $this->reportJsonSchemas[$clearingProcess->getId()]
      ??= $this->reportFormFactory
        ->createReportForm($this->getClearingProcessBundle($clearingProcess))->getJsonSchema();
  }

}
