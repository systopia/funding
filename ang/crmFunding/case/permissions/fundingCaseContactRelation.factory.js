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
 * Defines a service factory to access funding case relations via CiviCRM APIv4.
 *
 * @typedef {number} integer
 * @typedef {id: integer, fundingCaseId: integer, type: string, properties: json, permissions: string[]} ContactRelation
 *   Value of "properties" depends on the type.
 * @typedef {fundingCaseId: integer, type: string, properties: json, permissions: string[]} NewContactRelation
 *   Newly added contact relations do not have an id.
 */
fundingModule.factory('fundingCaseContactRelationService', ['crmApi4', function(crmApi4) {
  return {
    /**
     * Get all configured contact relations for the given funding case ID.
     *
     * @param {integer} fundingCaseId
     * @returns {Promise<ContactRelation[]>}
     */
    getAll: (fundingCaseId) => crmApi4('FundingCaseContactRelation', 'get',
        {where:[['funding_case_id', '=', fundingCaseId]]}),

    /**
     * Replaces all contact relations for the given funding case ID with
     * the given relations.
     *
     * @param {integer} fundingCaseId
     * @param {(ContactRelation|NewContactRelation)[]} relations
     * @returns {Promise<ContactRelation[]>}
     */
    replaceAll: (fundingCaseId, relations) => crmApi4('FundingCaseContactRelation', 'replace',
        {where: [['funding_case_id', '=', fundingCaseId]], records: relations }),

    /**
     * @returns {object} with key as permission and value as label.
     */
    getPossiblePermissions: () => crmApi4('FundingCaseContactRelation', 'getFields', {
      loadOptions: true,
      where: [["name", "=", "permissions"]],
      select: ["options"]
    }).then(function(result) {
      return result[0].options;
    }),
  };
}]);
