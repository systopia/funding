<?php
declare(strict_types = 1);

namespace Civi\Api4;

use Civi\Funding\Api4\AbstractRemoteFundingEntity;
use Civi\Funding\Api4\Action\Remote\FundingClearingProcess\GetAction;
use Civi\Funding\Api4\Action\Remote\FundingClearingProcess\GetOrCreateAction;
use Civi\Funding\Api4\Action\Remote\FundingClearingProcess\GetFormAction;
use Civi\Funding\Api4\Action\Remote\FundingClearingProcess\SubmitFormAction;
use Civi\Funding\Api4\Action\Remote\FundingClearingProcess\ValidateFormAction;

/**
 * FundingClearingProcess entity.
 *
 * Provided by the Funding Program Manager extension.
 *
 * @package Civi\Api4
 */
final class RemoteFundingClearingProcess extends AbstractRemoteFundingEntity {

  public static function get(): GetAction {
    return new GetAction();
  }

  public static function getOrCreate(): GetOrCreateAction {
    return new GetOrCreateAction();
  }

  public static function getForm(): GetFormAction {
    return new GetFormAction();
  }

  public static function validateForm(): ValidateFormAction {
    return new ValidateFormAction();
  }

  public static function submitForm(): SubmitFormAction {
    return new SubmitFormAction();
  }

}
