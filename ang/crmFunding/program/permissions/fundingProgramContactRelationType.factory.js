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
 * Defines a service factory to access funding program contact relation types via
 * CiviCRM APIv4.
 *
 * @typedef {Object} ContactRelationType
 * @property {string} name
 * @property {string} label
 * @property {string} template AngularJS template to display the type specific properties.
 * @property {string} help Help text for the type. Might be empty.
 * @property {json} extra Possible type specific extra properties.
 */
fundingModule.factory('fundingProgramContactRelationTypeService', ['crmApi4', function(crmApi4) {
  return {
    /**
     * @returns {Promise<ContactRelationType[]>}
     */
    getAll: () => crmApi4('FundingProgramContactRelationType', 'get', {orderBy: {label: 'ASC'}}, 'name'),
  };
}]);
