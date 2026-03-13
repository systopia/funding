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

namespace Civi\Funding\DependencyInjection\Compiler;

use Civi\Funding\DependencyInjection\Compiler\Traits\FundingCaseTypeServiceCollectorTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @codeCoverageIgnore
 */
final class FundingCaseTypeServicePass implements CompilerPassInterface {

  use FundingCaseTypeServiceCollectorTrait;

  /**
   * @var class-string<\Civi\Funding\FundingCaseType\AbstractFundingCaseTypeServiceCollector>
   * @phpstan-ignore missingType.generics
   */
  private string $collectorClass;

  /**
   * @var class-string<\Civi\Funding\FundingCaseType\FundingCaseTypeServiceInterface>
   */
  private string $serviceInterface;

  private bool $serviceForAllFundingCaseTypesRequired;

  private bool $public = FALSE;

  /**
   * @param class-string<\Civi\Funding\FundingCaseType\AbstractFundingCaseTypeServiceCollector> $collectorClass
   * @param class-string<\Civi\Funding\FundingCaseType\FundingCaseTypeServiceInterface> $serviceInterface
   *
   * @phpstan-ignore missingType.generics
   */
  public function __construct(
    string $collectorClass,
    string $serviceInterface,
    bool $serviceForAllFundingCaseTypesRequired = FALSE
  ) {
    $this->collectorClass = $collectorClass;
    $this->serviceInterface = $serviceInterface;
    $this->serviceForAllFundingCaseTypesRequired = $serviceForAllFundingCaseTypesRequired;
  }

  /**
   * @inheritDoc
   */
  public function process(ContainerBuilder $container): void {
    $services = $this->registerCollector(
      $container, $this->collectorClass, $this->serviceInterface, public: $this->public
    );

    if ($this->serviceForAllFundingCaseTypesRequired) {
      $missingServices = array_diff(FundingCaseTypeMetaDataPass::$fundingCaseTypes, array_keys($services));
      if ([] !== $missingServices) {
        throw new \RuntimeException(sprintf(
          'Implementation of %s is missing for the following funding case types: %s',
          $this->serviceInterface,
          implode(', ', $missingServices),
        ));
      }
    }
  }

  public function setPublic(bool $public): self {
    $this->public = $public;

    return $this;
  }

}
