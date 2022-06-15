<?php
declare(strict_types = 1);

use Webmozart\Assert\Assert;

/**
 * Required for create/update in APIv4 explorer when FundingProgram is
 * referenced.
 */

/**
 * @param array<string, mixed> $request
 *
 * @see _civicrm_api3_generic_getlist_params
 */
function _civicrm_api3_funding_program_getlist_params(array &$request): void {
  _civicrm_api3_generic_getlist_params($request);
}

/**
 * @param array<string, mixed> $result
 * @param array<string, mixed> $request
 *
 * @return array<int, array<string, mixed>>
 *
 * @see _civicrm_api3_generic_getlist_output
 */
function _civicrm_api3_funding_program_getlist_output(array $result, array $request): array {
  return _civicrm_api3_generic_getlist_output($result, $request, 'FundingProgram', []);
}

/**
 * @param array<string, mixed> $params
 *
 * @return array<string, mixed>
 *
 * @see _civicrm_api3_basic_get
 */
function civicrm_api3_funding_program_get(array $params): array {
  $bao = _civicrm_api3_get_BAO(__FUNCTION__);
  Assert::notNull($bao);

  return _civicrm_api3_basic_get($bao, $params, TRUE, 'FundingProgram');
}
