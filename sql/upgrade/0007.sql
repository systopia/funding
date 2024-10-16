ALTER TABLE civicrm_funding_case
  ADD `notification_contact_ids` varchar(255) NOT NULL AFTER creation_contact_id;
