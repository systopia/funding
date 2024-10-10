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
