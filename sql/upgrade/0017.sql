ALTER TABLE `civicrm_funding_clearing_cost_item`
  MODIFY `payment_date` date,
  MODIFY `payment_party` varchar(255),
  MODIFY `reason` varchar(255),
  ADD `properties` text,
  ADD `form_key` varchar(255) NOT NULL;

ALTER TABLE `civicrm_funding_clearing_resources_item`
  MODIFY `payment_date` date,
  MODIFY `payment_party` varchar(255),
  MODIFY `reason` varchar(255),
  ADD `properties` text,
  ADD `form_key` varchar(255) NOT NULL;
