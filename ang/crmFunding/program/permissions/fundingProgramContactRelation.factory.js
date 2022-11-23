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
 * Defines a service factory to access funding program relations via CiviCRM APIv4.
 *
 * @typedef {number} integer
 * @typedef {id: integer, fundingProgramId: integer, type: string, properties: json, permissions: string[]} ContactRelation
 *   Value of "properties" depends on the type.
 * @typedef {fundingProgramId: integer, type: string, properties: json, permissions: string[]} NewContactRelation
 *   Newly added contact relations do not have an id.
 */
fundingModule.factory('fundingProgramContactRelationService', ['crmApi4', function(crmApi4) {
  return {
    /**
     * Get all configured contact relations for the given funding program ID.
     *
     * @param {integer} fundingProgramId
     * @returns {Promise<ContactRelation[]>}
     */
    getAll: (fundingProgramId) => crmApi4('FundingProgramContactRelation', 'get',
        {where:[['funding_program_id', '=', fundingProgramId]]}),

    /**
     * Replaces all contact relations for the given funding program ID with
     * the given relations.
     *
     * @param {integer} fundingProgramId
     * @param {(ContactRelation|NewContactRelation)[]} relations
     * @returns {Promise<ContactRelation[]>}
     */
    replaceAll: (fundingProgramId, relations) => crmApi4('FundingProgramContactRelation', 'replace',
        {where: [['funding_program_id', '=', fundingProgramId]], records: relations }),

    /**
     * @returns {object} with key as permission and value as label.
     */
    getPossiblePermissions: () => crmApi4('FundingProgramContactRelation', 'getFields', {
      loadOptions: true,
      where: [["name", "=", "permissions"]],
      select: ["options"]
    }).then(function(result) {
      return result[0].options;
    }),
  };
}]);
