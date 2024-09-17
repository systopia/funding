<?php
declare(strict_types = 1);

namespace Civi\Funding\Mock\Contact;

use Civi\Funding\Contact\PossibleRecipientsLoaderInterface;
use Civi\Funding\Entity\FundingProgramEntity;

final class PossibleRecipientsLoaderMock implements PossibleRecipientsLoaderInterface {

  /**
   * @phpstan-var array<int, string>
   */
  public static array $possibleRecipients = [];

  /**
   * @inheritDoc
   */
  public function getPossibleRecipients(int $contactId, FundingProgramEntity $fundingProgram): array {
    return self::$possibleRecipients;
  }

}
