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

namespace Civi\Funding\Permission\FundingCase\RelationFactory;

use Civi\Funding\Entity\FundingCaseContactRelationEntity;
use Civi\Funding\Entity\FundingNewCasePermissionsEntity;
use Civi\Funding\EntityFactory\FundingCaseFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Permission\FundingCase\RelationFactory\FundingCaseContactRelationFactory
 */
final class FundingCaseContactRelationFactoryTest extends TestCase {

  /**
   * @var \Civi\Funding\Permission\FundingCase\RelationFactory\RelationPropertiesFactoryLocator&\PHPUnit\Framework\MockObject\MockObject)
   */
  private MockObject $propertiesFactoryLocatorMock;

  private FundingCaseContactRelationFactory $factory;

  protected function setUp(): void {
    parent::setUp();
    $this->propertiesFactoryLocatorMock = $this->createMock(RelationPropertiesFactoryLocator::class);
    $this->factory = new FundingCaseContactRelationFactory($this->propertiesFactoryLocatorMock);
  }

  public function testCreateFundingCaseContactRelation(): void {
    $properties = ['foo' => 'bar'];
    $fundingCase = FundingCaseFactory::createFundingCase();

    $propertiesFactoryMock = $this->createMock(RelationPropertiesFactoryInterface::class);
    $propertiesFactoryMock->method('getRelationType')->willReturn('TestRelationType');
    $propertiesFactoryMock->method('createRelationProperties')->with($properties, $fundingCase)
      ->willReturn(['foo' => 'baz']);

    $this->propertiesFactoryLocatorMock->method('get')->with('TestType')->willReturn($propertiesFactoryMock);

    $newCasePermissions = FundingNewCasePermissionsEntity::fromArray([
      'type' => 'TestType',
      'properties' => $properties,
      'permissions' => ['test_permission'],
    ]);

    $relation = $this->factory->createFundingCaseContactRelation($newCasePermissions, $fundingCase);
    static::assertEquals(FundingCaseContactRelationEntity::fromArray([
      'funding_case_id' => $fundingCase->getId(),
      'type' => 'TestRelationType',
      'properties' => ['foo' => 'baz'],
      'permissions' => ['test_permission'],
    ]), $relation);
  }

}
