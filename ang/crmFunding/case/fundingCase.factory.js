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

fundingModule.factory('fundingCaseService', ['crmApi4', function(crmApi4) {
  /**
   * @param {integer} id
   * @param {string} field
   * @returns {Promise}
   */
  function getOptions(id, field) {
    return crmApi4('FundingCase', 'getFields', {
      loadOptions: true,
      values: {id},
      where: [['name', '=', field]],
      select: ['options']
    }).then((result) => result[0].options || {});
  }

  return {
    /**
     * @param {integer} id
     * @param {number} amount
     * @returns {Promise}
     */
    approve: (id, amount) => crmApi4('FundingCase', 'approve', {id, amount})
        .then((result) => result),

    /**
     * @param {integer} id
     * @returns {Promise}
     */
    get: (id) => crmApi4('FundingCase', 'get', {
      where: [['id', '=', id]],
    }).then(function (result) {
      return result[0] || null;
    }),


    /**
     * @param {integer} id
     * @returns {Promise<string[]>}
     */
    getPossibleActions: (id) => crmApi4('FundingCase', 'getPossibleActions', {id}),

    /**
     * @param {integer} id
     * @returns {Promise<string[]>}
     */
    getStatusLabels: (id) => getOptions(id, 'status'),

    /**
     * @param {integer} id
     * @param {string} field
     * @param value
     * @returns {Promise}
     */
    setValue: (id, field, value) => {
      let params = {where: [['id', '=', id]], values: {}};
      params.values[field] = value;

      return crmApi4('FundingCase', 'update', params);
    },

    /**
     * @param {integer} id
     *
     * @returns {Promise}
     */
    recreateTransferContract:
        (id) => crmApi4('FundingCase', 'recreateTransferContract', {id}),

    /**
     * @param {integer} id
     * @param {number} amount
     * @returns {Promise<object>}
     */
    updateAmountApproved: (id, amount) => crmApi4('FundingCase', 'updateAmountApproved', {id, amount}),
  };
}]);
