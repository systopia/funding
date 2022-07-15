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

/**
 * @noinspection PhpUnhandledExceptionInspection
 */

declare(strict_types = 1);

namespace Civi\Api4;

use Civi\Api4\Traits\FundingCaseTestFixturesTrait;
use Civi\Test;
use Civi\Test\CiviEnvBuilder;
use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;
use PHPUnit\Framework\TestCase;

/**
 * @group headless
 *
 * @covers \Civi\Api4\RemoteFundingCase
 * @covers \Civi\Funding\Api4\Action\FundingCase\GetAction
 * @covers \Civi\Funding\EventSubscriber\FundingCasePermissionsGetSubscriber
 */
final class FundingCaseTest extends TestCase implements HeadlessInterface, TransactionalInterface {

  use FundingCaseTestFixturesTrait;

  protected int $associatedContactId;

  protected int $associatedContactIdNoPermissions;

  protected int $relatedABContactId;

  protected int $relatedBAContactId;

  protected int $permittedFundingCaseId;

  protected int $notPermittedContactId;

  public function setUpHeadless(): CiviEnvBuilder {
    return Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  protected function setUp(): void {
    parent::setUp();
    $this->addFixtures();
  }

  public function testPermissions(): void {
    // Contact is directly associated
    $permittedAssociatedResult = RemoteFundingCase::get()
      ->setRemoteContactId((string) $this->associatedContactId)
      ->execute();
    static::assertSame(1, $permittedAssociatedResult->rowCount);
    static::assertSame($this->permittedFundingCaseId, $permittedAssociatedResult->first()['id']);
    static::assertSame(['foo', 'bar'], $permittedAssociatedResult->first()['permissions']);
    static::assertTrue($permittedAssociatedResult->first()['PERM_foo']);
    static::assertTrue($permittedAssociatedResult->first()['PERM_bar']);

    // Contact has an a-b-relationship with an associated contact
    $permittedABResult = RemoteFundingCase::get()
      ->setRemoteContactId((string) $this->relatedABContactId)
      ->execute();
    static::assertSame(1, $permittedABResult->rowCount);
    static::assertSame($this->permittedFundingCaseId, $permittedABResult->first()['id']);
    static::assertSame(['c', 'd'], $permittedABResult->first()['permissions']);
    static::assertTrue($permittedABResult->first()['PERM_c']);
    static::assertTrue($permittedABResult->first()['PERM_d']);

    // Contact has an b-a-relationship with an associated contact
    $permittedBAResult = RemoteFundingCase::get()
      ->setRemoteContactId((string) $this->relatedBAContactId)
      ->execute();
    static::assertSame(1, $permittedBAResult->rowCount);
    static::assertSame($this->permittedFundingCaseId, $permittedBAResult->first()['id']);
    static::assertSame(['c', 'd'], $permittedBAResult->first()['permissions']);
    static::assertTrue($permittedBAResult->first()['PERM_c']);
    static::assertTrue($permittedBAResult->first()['PERM_d']);

    // Contact has a not permitted relationship with an associated contact
    $notPermittedResult = RemoteFundingCase::get()
      ->setRemoteContactId((string) $this->notPermittedContactId)
      ->execute();
    static::assertSame(0, $notPermittedResult->rowCount);

    // Contact is directly associated, but has no permissions set
    $permittedAssociatedResult = RemoteFundingCase::get()
      ->setRemoteContactId((string) $this->associatedContactIdNoPermissions)
      ->execute();
    static::assertSame(0, $permittedAssociatedResult->rowCount);
  }

}
