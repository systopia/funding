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

namespace Civi\Funding\DependencyInjection\Compiler;

use Civi\Funding\DependencyInjection\Compiler\Traits\TaggedFundingCaseTypeServicesTrait;
use Civi\Funding\FundingCaseType\FundingCaseTypeMetaDataProvider;
use Civi\Funding\FundingCaseType\FundingCaseTypeMetaDataProviderInterface;
use Civi\Funding\FundingCaseType\MetaData\FundingCaseTypeMetaDataInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class FundingCaseTypeMetaDataPass implements CompilerPassInterface {

  use TaggedFundingCaseTypeServicesTrait;

  /**
   * @inheritDoc
   */
  public function process(ContainerBuilder $container): void {
    $services = $this->getTaggedFundingCaseTypeServices($container, FundingCaseTypeMetaDataInterface::class);
    $container->register(FundingCaseTypeMetaDataProviderInterface::class, FundingCaseTypeMetaDataProvider::class)
      ->addArgument(ServiceLocatorTagPass::register($container, $services))
      ->addArgument(array_keys($services))
      ->setPublic(TRUE);
  }

  /**
   * @phpstan-param array{name?: string} $attributes
   *
   * @phpstan-return array{string}
   *
   * @throws \RuntimeException
   */
  protected function getFundingCaseTypes(ContainerBuilder $container, string $id, array $attributes): array {
    if (array_key_exists('name', $attributes)) {
      return [$attributes['name']];
    }

    $constantName = $this->getServiceClass($container, $id) . '::NAME';
    if (defined($constantName)) {
      // @phpstan-ignore return.type
      return [constant($constantName)];
    }

    throw new \RuntimeException(sprintf('Could not find funding case type name for service "%s"', $id));
  }

}
