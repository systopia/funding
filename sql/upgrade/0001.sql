ALTER TABLE civicrm_funding_application_snapshot
  ADD cost_items text NOT NULL AFTER request_data;

ALTER TABLE civicrm_funding_app_cost_item
  ADD data_pointer varchar(255) NOT NULL COMMENT 'JSON pointer to data in application data';

UPDATE civicrm_funding_app_cost_item SET identifier = REPLACE(identifier, '/', '.');
