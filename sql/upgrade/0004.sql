ALTER TABLE civicrm_funding_application_snapshot
  ADD custom_fields mediumtext NOT NULL;

-- Status "closed" has been replaced by "withdrawn" and "rejected".
UPDATE civicrm_funding_case SET
  status = IF(
    'withdrawn' = (SELECT status FROM civicrm_funding_application_process
                    WHERE funding_case_id = civicrm_funding_case.id
                    ORDER BY modification_date DESC
                    LIMIT 1),
    'withdrawn',
    'rejected')
  WHERE status = 'closed';

-- The amount approved of a funding case might have been changed without the
-- amount total of the corresponding payout process being updated.
-- The status might be closed, through a previous subscriber, but it now gets
-- closed when finishing clearing of a funding case.
UPDATE civicrm_funding_payout_process p SET
  p.amount_total = (SELECT c.amount_approved FROM civicrm_funding_case c WHERE c.id = p.funding_case_id),
  p.status = 'open';
