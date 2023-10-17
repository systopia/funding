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

namespace Civi\Funding\Api4\Action\Remote\ApplicationProcess;

use Civi\Core\CiviEventDispatcherInterface;
use Civi\Funding\Api4\Action\Remote\FundingCase\Traits\NewApplicationFormActionTrait;
use Civi\Funding\ApplicationProcess\ApplicationProcessBundleLoader;
use Civi\Funding\Entity\ApplicationProcessEntityBundle;
use Civi\Funding\EntityFactory\ApplicationProcessBundleFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ClockMock;

abstract class AbstractFormActionTestCase extends TestCase {

  protected const REMOTE_CONTACT_ID = '00';

  protected const CONTACT_ID = 11;

  protected ApplicationProcessEntityBundle $applicationProcessBundle;

  /**
   * @var \Civi\Core\CiviEventDispatcherInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  protected $eventDispatcherMock;

  /**
   * @var \Civi\Funding\ApplicationProcess\ApplicationProcessBundleLoader&\PHPUnit\Framework\MockObject\MockObject
   */
  protected MockObject $applicationProcessBundleLoaderMock;

  public static function setUpBeforeClass(): void {
    parent::setUpBeforeClass();
    ClockMock::register(__CLASS__);
    ClockMock::register(NewApplicationFormActionTrait::class);
    ClockMock::withClockMock(strtotime('1970-01-02'));
  }

  protected function setUp(): void {
    parent::setUp();
    $this->applicationProcessBundleLoaderMock = $this->createMock(ApplicationProcessBundleLoader::class);
    $this->eventDispatcherMock = $this->createMock(CiviEventDispatcherInterface::class);

    $this->applicationProcessBundle = ApplicationProcessBundleFactory::createApplicationProcessBundle(
      ['id' => 22],
      [],
      [],
      [
        'requests_start_date' => date('Y-m-d', time() - 86400),
        'requests_end_date' => date('Y-m-d', time() + 86400),
      ],
    );

    $this->applicationProcessBundleLoaderMock->method('get')->with(22)->willReturn($this->applicationProcessBundle);
  }

}
