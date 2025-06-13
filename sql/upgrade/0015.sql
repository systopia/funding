ALTER TABLE civicrm_funding_clearing_cost_item
  ADD receipt_date date AFTER receipt_number;

ALTER TABLE civicrm_funding_clearing_resources_item
  ADD receipt_date date AFTER receipt_number;
