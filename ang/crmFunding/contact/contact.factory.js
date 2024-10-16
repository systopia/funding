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

fundingModule.factory('fundingContactService', ['crmApi4', function(crmApi4) {
  return {
    get: (id) => crmApi4('Contact', 'get', {
      where: [['id', '=', id]],
    }).then(function (result) {
      return result[0] || null;
    }),

    /**
     * @param {string} input
     * @returns {Promise<{id: integer, label: string}[]>}
     */
    autocomplete: (input) => crmApi4('Contact', 'autocomplete', {input}),

    /**
     * @param {integer[]} ids
     * @returns {Promise<{id: integer, label: string}[]>}
     */
    autocompleteByIds: (ids) => crmApi4('Contact', 'autocomplete', {ids}),
  };
}]);
