<?php

declare(strict_types = 1);

namespace Civi\Funding\Api4\Action\FundingProgram;

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

    $result = (new CloneAction())
      ->setCheckPermissions(FALSE)
      ->addWhere('id', '=', $fundingProgram->getId())
      ->execute();

    static::assertCount(1, $result);
    $clonedProgram = $result->first();
    static::assertNotEquals($fundingProgram->getId(), $clonedProgram['id']);
    static::assertStringStartsWith('Copy of ', $clonedProgram['title']);
  }

  public function testSetValuesAndGetValues(): void {
    $action = new CloneAction();
    $values = ['title' => 'New Title', 'abbreviation' => 'NEW'];
    $action->setValues($values);
    static::assertEquals($values, $action->getValues());
  }

}
