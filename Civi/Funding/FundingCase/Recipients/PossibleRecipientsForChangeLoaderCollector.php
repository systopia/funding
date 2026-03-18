<?php
declare(strict_types = 1);

namespace Civi\Funding\FundingCase\Recipients;

use Civi\Funding\Entity\FundingCaseBundle;
use Civi\Funding\FundingCaseType\AbstractFundingCaseTypeServiceCollector;

/**
 * @extends AbstractFundingCaseTypeServiceCollector<PossibleRecipientsForChangeLoaderInterface>
 */
// phpcs:ignore Generic.Files.LineLength.TooLong
final class PossibleRecipientsForChangeLoaderCollector extends AbstractFundingCaseTypeServiceCollector implements PossibleRecipientsForChangeLoaderInterface {

  /**
   * @inheritDoc
   */
  public function getPossibleRecipients(FundingCaseBundle $fundingCaseBundle): array {
    return $this
      ->getService($fundingCaseBundle->getFundingCaseType()->getName())
      ->getPossibleRecipients($fundingCaseBundle);
  }

}
