/*
 * Copyright (C) 2022 SYSTOPIA GmbH
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

'use strict';

/**
 * Defines a service factory to access recipient relations via CiviCRM APIv4.
 *
 * @typedef {number} integer
 * @typedef {id: integer, fundingProgramId: integer, type: string, properties: json} RecipientRelation
 *   Value of "properties" depends on the type.
 * @typedef {fundingProgramId: integer, type: string, properties: json} NewRecipientRelation
 *   Newly added recipient relations do not have an id.
 */
fundingModule.factory('recipientRelationService', ['crmApi4', function(crmApi4) {
  return {
    /**
     * Get all configured recipient relations for the given funding program ID.
     *
     * @param {integer} fundingProgramId
     * @returns {Promise<RecipientRelation[]>}
     */
    getAll: (fundingProgramId) => crmApi4('FundingRecipientContactRelation', 'get',
        {where:[['funding_program_id', '=', fundingProgramId]]}),

    /**
     * Replaces all recipient relations for the given funding program ID with
     * the given relations.
     *
     * @param {integer} fundingProgramId
     * @param {(RecipientRelation|NewRecipientRelation)[]} relations
     * @returns {Promise<RecipientRelation[]>}
     */
    replaceAll: (fundingProgramId, relations) => crmApi4('FundingRecipientContactRelation', 'replace',
        {where: [['funding_program_id', '=', fundingProgramId]], records: relations }),
  };
}]);
