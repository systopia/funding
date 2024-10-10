-- The amount approved of a funding case might have been changed without the
-- amount total of the corresponding payout process being updated. Now there's
-- code that does that, but for existing payout processes we have to correct it.
--
-- The status might be closed, through a subscriber in previous versions (when
-- the amount draw downed was equal to amount_total), but it  now gets closed
-- when finishing clearing of a funding case. Thus, all existing payout
-- processes have to be set to "open" to be able to perform the finishing of the
-- clearing.
UPDATE civicrm_funding_payout_process p SET
  p.amount_total = (SELECT c.amount_approved FROM civicrm_funding_case c WHERE c.id = p.funding_case_id),
  p.status = 'open';
