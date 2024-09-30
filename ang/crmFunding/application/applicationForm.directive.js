/*
 * Copyright (C) 2024 SYSTOPIA GmbH
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

fundingModule.directive('fundingApplicationForm', ['$compile', function($compile) {
  return {
    restrict: 'AE',
    scope: false,
    template: '',
    // Insert the application form for the current funding case type.
    link: function (scope, element) {
      const unwatch = scope.$watch('fundingCaseType', function (fundingCaseType) {
        if (fundingCaseType) {
          const tagName = _4.get(fundingCaseType, 'properties.applicationFormTagName', 'funding-jf-form');
          const template = '<' + tagName +
            ' json-schema="jsonSchema" ui-schema="uiSchema" data="data"' +
            ' errors="errors" editable="isEditAllowed()"' +
            ' on-start-edit="onStartEdit" on-before-save="onBeforeSave"' +
            ' on-after-save="onAfterSave" on-cancel-edit="onCancelEdit"' +
            ' on-edit-finished="onEditFinished" add-to="addTo" inserted="inserted"' +
            ' cancel-insert-at="cancelInsertAt" remove-at="removeAt"></' + tagName + '>';
          element.append($compile(template)(scope));
          unwatch();
        }
      });
    },
  };
}]);
