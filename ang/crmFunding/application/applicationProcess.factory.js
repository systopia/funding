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

fundingModule.factory('fundingApplicationProcessService', ['crmApi4', function(crmApi4) {
  function getOptionLabels(id, field) {
    return crmApi4('FundingApplicationProcess', 'getFields', {
      loadOptions: true,
      values: {id},
      where: [['name', '=', field]],
      select: ['options']
    }).then((result) => result[0].options || {});
  }

  /**
   * @param {object} values
   * @param {string} field
   *
   * @returns {Promise<object[]>}
   *   Options with option name as index.
   */
  function getOptions(values, field) {
    return crmApi4('FundingApplicationProcess', 'getFields', {
      loadOptions: [
        'id',
        'name',
        'label',
        'abbr',
        'description',
        'color',
        'icon',
      ],
      values: values,
      where: [['name', '=', field]],
      select: ['options']
    }).then(function (result) {
      const options = result[0].options || [];
      const optionsByName = {};
      options.forEach((option) => {
        optionsByName[option.name] = option;
      });

      return optionsByName;
    });
  }

  return {
    get: (id) => crmApi4('FundingApplicationProcess', 'get', {
      where: [['id', '=', id]],
    }).then(function (result) {
      return result[0] || null;
    }),

    /**
     * @param {integer} fundingCaseId
     * @returns {Promise}
     */
    getByFundingCaseId: (fundingCaseId) => crmApi4('FundingApplicationProcess', 'get', {
      where: [['funding_case_id', '=', fundingCaseId]],
    }),

    /**
     * @param {integer} id
     * @returns {Promise}
     */
    getForm: (id) => crmApi4('FundingApplicationProcess', 'getForm', {id}),

    getFormData: (id) => crmApi4('FundingApplicationProcess', 'getFormData', {id}).then(function (result) {
      return result.data || null;
    }),

    /**
     * @param {array<integer>} ids
     * @returns {Promise}
     */
    getAllowedActionsMultiple: (ids) => crmApi4('FundingApplicationProcess', 'getAllowedActionsMultiple', {ids}),

    getJsonSchema: (id) => crmApi4('FundingApplicationProcess', 'getJsonSchema', {id}).then(function (result) {
      return result.jsonSchema || null;
    }),
    setValue: (id, field, value) => {
      let params = {where: [['id', '=', id]], values: {}};
      params.values[field] = value;

      return crmApi4('FundingApplicationProcess', 'update', params);
    },
    submitForm: (id, data) => crmApi4('FundingApplicationProcess', 'submitForm', {id, data}),
    validateForm: (id, data) => crmApi4('FundingApplicationProcess', 'validateForm', {id, data}),

    /**
     * @param {array<integer>} ids
     * @param {string} action
     * @returns {Promise}
     */
    applyActionMultiple: (ids, action) => crmApi4('FundingApplicationProcess', 'applyActionMultiple', {ids, action}),

    /**
     * @param {object} values
     *
     * @returns {Promise<object[]>}
     *   Options with option name as index.
     */
    getStatusOptions: (values) => getOptions(values, 'status'),
    getOptionLabels: (id, field) => getOptionLabels(id, field),
  };
}]);
