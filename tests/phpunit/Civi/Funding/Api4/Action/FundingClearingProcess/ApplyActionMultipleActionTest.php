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

namespace Civi\Funding\Api4\Action\FundingClearingProcess;

use Civi\API\Exception\UnauthorizedException;
use Civi\Api4\FundingClearingProcess;
use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\ClearingProcess\ClearingProcessPermissions;
use Civi\Funding\Entity\ClearingProcessEntityBundle;
use Civi\Funding\Fixtures\ClearingProcessBundleFixture;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Util\RequestTestUtil;
use Symfony\Bridge\PhpUnit\ClockMock;

/**
 * @covers \Civi\Api4\FundingClearingProcess
 * @covers \Civi\Funding\Api4\Action\FundingClearingProcess\ApplyActionMultipleAction
 * @covers \Civi\Funding\ClearingProcess\Api4\ActionHandler\ApplyActionMultipleActionHandler
 *
 * @group headless
 */
final class ApplyActionMultipleActionTest extends AbstractFundingHeadlessTestCase {

  private ClearingProcessEntityBundle $clearingProcessBundle;

  public static function setUpBeforeClass(): void {
    parent::setUpBeforeClass();
    ClockMock::register(__CLASS__);
    ClockMock::withClockMock(123456);
  }

  protected function setUp(): void {
    parent::setUp();

    $this->clearingProcessBundle = ClearingProcessBundleFixture::create([
      'status' => 'review-requested',
      'creation_date' => date('Y-m-d H:i:s', time() - 1),
      'modification_date' => date('Y-m-d H:i:s'),
    ]);
    $contact = ContactFixture::addIndividual();
    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $this->clearingProcessBundle->getFundingCase()->getId(),
      [ClearingProcessPermissions::REVIEW_CONTENT],
    );

    sleep(1);

    RequestTestUtil::mockInternalRequest($contact['id']);
  }

  public function testActionNotAllowed(): void {
    $this->expectException(UnauthorizedException::class);
    $this->expectExceptionMessage(sprintf(
      'Action "foo" is not allowed on clearing process with ID %d',
      $this->clearingProcessBundle->getClearingProcess()->getId()
    ));

    FundingClearingProcess::applyActionMultiple()
      ->setAction('foo')
      ->setIds([$this->clearingProcessBundle->getClearingProcess()->getId()])
      ->execute();
  }

  public function testValid(): void {
    $result = FundingClearingProcess::applyActionMultiple()
      ->setAction('review')
      ->setIds([$this->clearingProcessBundle->getClearingProcess()->getId()])
      ->execute()
      ->getArrayCopy();

    static::assertEquals([
      $this->clearingProcessBundle->getClearingProcess()->getId() => [
        'status' => 'review',
        'is_review_calculative' => NULL,
        'is_review_content' => NULL,
      ],
    ], $result);

    static::assertEquals([
      'id' => $this->clearingProcessBundle->getClearingProcess()->getId(),
      'status' => 'review',
      'modification_date' => date('Y-m-d H:i:s'),
    ],
      FundingClearingProcess::get(FALSE)
        ->addSelect('status', 'modification_date')
        ->addWhere('id', '=', $this->clearingProcessBundle->getClearingProcess()->getId())
        ->execute()
        ->single()
    );
  }

}
