<?php
/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingCaseContactRelationFixture;
use Civi\Funding\Fixtures\Traits\ClearingProcessFixturesTrait;
use Civi\Funding\Util\RequestTestUtil;

/**
 * @covers \Civi\Api4\FundingClearingProcess
 * @covers \Civi\Funding\Api4\Action\FundingClearingProcess\SetContentReviewerAction
 * @covers \Civi\Funding\ClearingProcess\Api4\ActionHandler\SetContentReviewerActionHandler
 *
 * @group headless
 */
final class SetContentReviewerActionTest extends AbstractFundingHeadlessTestCase {

  use ClearingProcessFixturesTrait;

  protected function setUp(): void {
    parent::setUp();
    $this->addFixtures();
  }

  public function test(): void {
    $contact = ContactFixture::addIndividual();

    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $this->clearingProcessBundle->getFundingCase()->getId(),
      ['review_test'],
    );
    RequestTestUtil::mockInternalRequest($contact['id']);

    $e = NULL;
    try {
      FundingClearingProcess::setContentReviewer()
        ->setClearingProcessId($this->clearingProcessBundle->getClearingProcess()->getId())
        ->setReviewerContactId($contact['id'])
        ->execute();
    }
    catch (UnauthorizedException $e) {
      static::assertSame('Permission to change content reviewer is missing.', $e->getMessage());
    }
    static::assertNotNull($e);

    FundingCaseContactRelationFixture::addContact(
      $contact['id'],
      $this->clearingProcessBundle->getFundingCase()->getId(),
      [ClearingProcessPermissions::REVIEW_CONTENT],
    );

    FundingClearingProcess::setContentReviewer()
      ->setClearingProcessId($this->clearingProcessBundle->getClearingProcess()->getId())
      ->setReviewerContactId($contact['id'])
      ->execute();

    static::assertSame($contact['id'], FundingClearingProcess::get(FALSE)
      ->addSelect('reviewer_cont_contact_id')
      ->addWhere('id', '=', $this->clearingProcessBundle->getClearingProcess()->getId())
      ->execute()->single()['reviewer_cont_contact_id']
    );

    $anotherContact = ContactFixture::addIndividual();
    $e = NULL;
    try {
      FundingClearingProcess::setContentReviewer()
        ->setClearingProcessId($this->clearingProcessBundle->getClearingProcess()->getId())
        ->setReviewerContactId($anotherContact['id'])
        ->execute();
    }
    catch (\Exception $e) {
      static::assertSame(
        sprintf('Contact %d is not allowed as content reviewer.', $anotherContact['id']),
        $e->getMessage()
      );
    }
    static::assertNotNull($e);
  }

}
