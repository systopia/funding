/*
 * Copyright (C) 2022 SYSTOPIA GmbH
 *
 *  This case is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation in version 3.
 *
 *  This case is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this case.  If not, see <http://www.gnu.org/licenses/>.
 */

'use strict';

/**
 * Defines a service factory to access new funding case permissions via CiviCRM APIv4.
 *
 * @typedef {number} integer
 * @typedef {id: integer, funding_program_id: integer, type: string, properties: json, permissions: string[]} NewCasePermissions
 *   Value of "properties" depends on the type.
 * @typedef {funding_program_id: integer, type: string, properties: json, permissions: string[]} NewNewCasePermissions
 *   Newly added permissions do not have an id.
 */
fundingModule.factory('fundingNewCasePermissionsService', ['crmApi4', function(crmApi4) {
  return {
    /**
     * Get all configured new funding case permissions for the given funding
     * program ID.
     *
     * @param {integer} fundingProgramId
     * @returns {Promise<NewCasePermissions[]>}
     */
    getAll: (fundingProgramId) => crmApi4('FundingNewCasePermissions', 'get',
        {where:[['funding_program_id', '=', fundingProgramId]]}),

    /**
     * Replaces all new funding case permissions for the given funding program
     * ID with the given permissions.
     *
     * @param {integer} fundingProgramId
     * @param {(NewCasePermissions|NewNewCasePermissions)[]} permissions
     * @returns {Promise<NewCasePermissions[]>}
     */
    replaceAll: (fundingProgramId, permissions) => crmApi4('FundingNewCasePermissions', 'replace',
        {where: [['funding_program_id', '=', fundingProgramId]], records: permissions }),

    /**
     * @returns {object} with key as permission and value as label.
     */
    getPossiblePermissions: () => crmApi4('FundingNewCasePermissions', 'getFields', {
      loadOptions: true,
      where: [["name", "=", "permissions"]],
      select: ["options"]
    }).then(function(result) {
      return result[0].options;
    }),
  };
}]);
