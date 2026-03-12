<?php
declare(strict_types = 1);

namespace Civi\Funding\FundingCase\Recipients;

use Civi\Funding\Entity\FundingCaseBundle;

/**
 * Loads the possible recipients when the recipient of an existing funding case
 * shall be changed via CiviCRM UI.
 */
interface PossibleRecipientsForChangeLoaderInterface {

  public const SERVICE_TAG = 'funding.case.possible_recipients_for_change_loader';

  /**
   * @phpstan-return array<int, string>
   *   Contact ID mapped to display name.
   */
  public function getPossibleRecipients(FundingCaseBundle $fundingCaseBundle): array;

}
