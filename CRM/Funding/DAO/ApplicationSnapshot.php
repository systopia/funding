<?php
/**
 * Copyright (C) 2026 SYSTOPIA GmbH
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation in version 3.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

/**
 * DAOs provide an OOP-style facade for reading and writing database records.
 *
 * DAOs are a primary source for metadata in older versions of CiviCRM (<5.74)
 * and are required for some subsystems (such as APIv3).
 *
 * This stub provides compatibility. It is not intended to be modified in a
 * substantive way. Property annotations may be added, but are not required.
 * @property string $id
 * @property string $application_process_id
 * @property string $status
 * @property string $creation_date
 * @property string $title
 * @property string $short_description
 * @property string $start_date
 * @property string $end_date
 * @property string $request_data
 * @property string $cost_items
 * @property string $resources_items
 * @property string $amount_requested
 * @property bool|string $is_review_content
 * @property bool|string $is_review_calculative
 * @property bool|string $is_eligible
 * @property bool|string $is_in_work
 * @property bool|string $is_rejected
 * @property bool|string $is_withdrawn
 * @property string $custom_fields
 */
class CRM_Funding_DAO_ApplicationSnapshot extends CRM_Funding_DAO_Base {

  /**
   * Required by older versions of CiviCRM (<5.74).
   * @var string
   */
  public static string $_tableName = 'civicrm_funding_application_snapshot';

}
