<?php
/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

namespace Civi\Funding\Api4\Action\RecipientContactRelationType;

use Civi\Api4\Generic\Result;
use Civi\Funding\Contact\Relation\AbstractRelationType;
use Civi\Funding\Contact\Relation\RelationTypeContainerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Civi\Funding\Api4\Action\RecipientContactRelationType\GetAction
 * @covers \Civi\Funding\Contact\Relation\AbstractRelationType
 */
final class GetActionTest extends TestCase {

  /**
   * @var \Civi\Funding\Contact\Relation\RelationTypeContainerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private MockObject $relationTypeContainerMock;

  private GetAction $action;

  protected function setUp(): void {
    parent::setUp();
    $this->relationTypeContainerMock = $this->createMock(RelationTypeContainerInterface::class);
    $this->action = new GetAction($this->relationTypeContainerMock);
  }

  public function testRun(): void {
    $relationTypeMock = $this->getMockForAbstractClass(AbstractRelationType::class);
    $relationTypeMock->method('getTemplate')->willReturn('Template');
    $relationTypeMock->method('getLabel')->willReturn('Label');
    $relationTypeMock->method('getName')->willReturn('Name');
    $relationTypeMock->method('getExtra')->willReturn(['foo' => 'bar']);
    $relationTypeMock->method('getHelp')->willReturn('Help');
    $this->relationTypeContainerMock->method('getRelationTypes')->willReturn([$relationTypeMock]);

    $result = new Result();
    $this->action->_run($result);

    static::assertEquals([
      [
        'name' => 'Name',
        'label' => 'Label',
        'template' => 'Template',
        'help' => 'Help',
        'extra' => ['foo' => 'bar'],
        'initialProperties' => new \stdClass(),
      ],
    ], $result->getArrayCopy());
  }

}
