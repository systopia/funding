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

// TODO: Interpret some JSON schema keywords

fundingModule.directive('editableField', [function() {
  function toJsonPointer(path) {
    return '/' + _4.join(_4.toPath(path), '/');
  }

  /**
   * @param {string} valueName
   * @returns {string}
   */
  function getPathFromValueName(valueName) {
    let path = _4.toPath(valueName);
    path.shift();
    if (path.length === 1) {
      return path[0];
    }

    return path.shift() + '[' + path.join('][') + ']';
  }

  return {
    restrict: 'E',
    transclude: true,
    scope: true,
    bindToController: {
      'path': '@?',
      'type': '@',
      'value': '@',
      'formName': '@?',
      'label': '=?',
      'editAllowed': '=?',
    },
    controllerAs: '$ctrl',
    controller: ['$scope', '$attrs', function ($scope, $attrs) {
      if ($attrs.formName === undefined && $scope.$parent.formName) {
        this.formName = $scope.$parent.formName;
      }
      if ($attrs.editAllowed === undefined) {
        this.editAllowed = $scope.$parent.$eval('isEditAllowed()');
        if (typeof this.editAllowed !== 'boolean') {
          this.editAllowed = true;
        }
      }
    }],
    link: function (scope, element, attrs, controller, transcludeFn) {
      transcludeFn(function (clone) {
        if (clone[0]) {
          // Display clone[0] instead of the value in the template
          angular.element(element[0].querySelectorAll('span')).html(clone);
        }
      });
    },
    template: function(element, attrs) {
      if (!attrs.path) {
        attrs.path = getPathFromValueName(attrs.value);
      }

      let template = '';
      if (attrs.label) {
        template += '<label>{{' + attrs.label + '}}</label> ';
      }

      const editSpan = angular.element('<span>{{' + attrs.value + '}}</span>');
      editSpan.attr('ng-show', '$ctrl.editAllowed');
      editSpan.attr('editable-' + attrs.type, attrs.value);
      editSpan.attr('e-name', attrs.path);
      editSpan.attr('e-form', '{{$ctrl.formName}}');
      editSpan.attr('onshow', 'onStartEdit(this)');
      editSpan.attr('onbeforesave', 'onBeforeSave(this)');
      editSpan.attr('onaftersave', 'onAfterSave(this)');
      editSpan.attr('oncancel', 'onCancelEdit(this)');
      editSpan.attr('onhide', 'onEditFinished(this)');
      for (let [key, value] of Object.entries(attrs)) {
        if (key === 'path' || key === 'type' || key === 'value' || key === 'formName' || key.startsWith('$')) {
          continue;
        }
        editSpan.attr(_4.kebabCase(key), value);
      }
      template += editSpan[0].outerHTML;

      const viewOnlySpan = angular.element('<span>{{' + attrs.value + '}}</span>');
      viewOnlySpan.attr('ng-show', '!$ctrl.editAllowed');
      template += viewOnlySpan[0].outerHTML;

      template += ' <funding-validation-errors errors="errors[\'' + toJsonPointer(attrs.path) + '\']"></funding-validation-errors>';

      return template;
    },
  };
}]);
