<?php

declare(strict_types = 1);

namespace Civi\Funding\Api4\Action\FundingProgram;

use Civi\Api4\Generic\Result;
use Civi\Funding\AbstractFundingHeadlessTestCase;
use Civi\Funding\Fixtures\ContactFixture;
use Civi\Funding\Fixtures\FundingProgramContactRelationFixture;
use Civi\Funding\Fixtures\FundingProgramFixture;

/**
 * @covers \Civi\Funding\Api4\Action\FundingProgram\CloneAction
 *
 * @group headless
 */
final class CloneActionTest extends AbstractFundingHeadlessTestCase {

  public function testCloneAction(): void {
    $fundingProgram = FundingProgramFixture::addFixture();

    $contact = ContactFixture::addIndividual();
    FundingProgramContactRelationFixture::addContact((int) $contact['id'], $fundingProgram->getId(), []);

    $action = new CloneAction();
    $action->setCheckPermissions(FALSE);
    $action->addWhere('id', '=', $fundingProgram->getId());

    $result = new Result();
    $action->_run($result);

    static::assertCount(1, $result);
    $clonedProgram = $result->first();
    static::assertNotEquals($fundingProgram->getId(), $clonedProgram['id']);
    static::assertStringStartsWith('Copy of ', $clonedProgram['title']);
  }

}
