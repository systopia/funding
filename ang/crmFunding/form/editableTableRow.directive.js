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

fundingModule.directive('fundingEditableTableRow', function() {
  function toJsonPointer(path) {
    return '/' + _4.join(_4.toPath(path), '/');
  }

  /**
   * @param {string} expression
   * @returns {string}
   */
  function getPathFromExpression(expression) {
    let path = _4.toPath(expression);
    path.shift();
    if (path.length === 1) {
      return path[0];
    }

    return path.shift() + '[' + path.join('][') + ']';
  }

  return {
    restrict: 'A',
    transclude: true,
    scope: true,
    bindToController: {
      'formName': '@?',
      'path': '@?',
      'inserted': '=?',
      'editAllowed': '=?',
    },
    controllerAs: '$ctrl',
    controller: ['$scope', '$attrs', function ($scope, $attrs) {
      if ($attrs.editAllowed === undefined) {
        $attrs.editAllowed = 'isEditAllowed()';
      }
      if ($attrs.ngRepeat) {
        // "row in ..." or "(key, row) in ..."
        const regex = new RegExp('^((?<rowName1>[^ ]+)|\([^,]+, (?<rowName2>[^\)]+)\)) in (?<expression>[^ ]+)( |$)');
        const result = regex.exec($attrs.ngRepeat);
        const rowName = result.groups.rowName1 || result.groups.rowName2;
        this.row = $scope[rowName];
        if (!$attrs.path) {
          const expression = result.groups.expression;
          this.path = getPathFromExpression(expression);
        }
      } else if (!$attrs.formName) {
        throw new Error('Attribute form-name is missing');
      } else {
        this.row = null;
      }

      const path = $attrs.path || this.path;
      if (path) {
        this.jsonPointer = toJsonPointer(path);
        if (!$attrs.formName) {
          this.formName = path
              .replaceAll('[', '')
              .replaceAll(']', '')
              .replaceAll('.', '') + 'Form';
        }
      }

      $scope.formName = $attrs.formName || this.formName;

      const self = this;
      this.form = function () {
        return $scope[self.formName];
      };

      this.insertedRow = function() {
        return self.inserted || $scope.$parent.inserted;
      };
    }],
    link: function (scope, element, attrs, controller, transcludeFn) {
      const newScope = scope.$new(false);
      transcludeFn(newScope, function (clone) {
        // Find element in transcluded content to insert action buttons.
        const selector = '.editable-table-row-actions';
        let rowActionsElem;
        for (let cloneChild of clone) {
          if (cloneChild instanceof Element) {
            rowActionsElem = cloneChild.matches(selector) ? cloneChild : cloneChild.querySelector(selector);
            if (rowActionsElem) {
              break;
            }
          }
        }

        // Add action buttons to rowActionsElem if found in transcluded content
        // and replace content of html with transcluded intent, otherwise
        // prepend transcluded content to action buttons column.
        if (rowActionsElem) {
          rowActionsElem = angular.element(rowActionsElem);
          rowActionsElem.html(element.contents());
          element.html(clone);
        } else {
          element.prepend(clone);
        }
      });
    },
    templateUrl: '~/crmFunding/form/editableTableRow.template.html',
  };
});
