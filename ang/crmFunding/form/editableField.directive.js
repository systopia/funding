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
      // Will be displayed if model value is empty.
      'emptyValueDisplay': '@',
      // The "oneOf" attribute from JSON schema for fields with options to
      // select from.
      'optionsOneOf': '=?',
      'formName': '@?',
      'label': '=?',
      'editAllowed': '=?',
    },
    controllerAs: '$ctrl',
    controller: ['$scope', '$attrs', function ($scope, $attrs) {
      const ts = CRM.ts('funding');
      if ($attrs.emptyValueDisplay === undefined) {
        this.emptyValueDisplay = $attrs.emptyValueDisplay = ts('empty');
      }
      if ($attrs.formName === undefined && $scope.$parent.formName) {
        this.formName = $scope.$parent.formName;
      }
      if ($attrs.editAllowed === undefined) {
        $attrs.editAllowed = 'isEditAllowed()';
      }

      $scope.showCheckbox = function (checked) {
        if (checked === undefined || checked === null) {
          return $attrs.emptyValueDisplay;
        }

        return checked ? ts('Yes') : ts('No');
      };

      /**
       * @param {array} selected
       * @param {object[]} oneOf
       * @returns {string}
       */
      $scope.showChecklist = function(selected, oneOf) {
        if (selected === undefined || selected === null || selected.length === 0) {
          return $attrs.emptyValueDisplay;
        }

        let labels = [];
        oneOf.forEach(function (value) {
          if (selected.includes(value.const)) {
            labels.push(value.title);
          }
        });

        return labels.join(', ');
      };

      /**
       * @param {string} selected
       * @param {object[]} oneOf
       * @returns {string}
       */
      $scope.showSelect = function(selected, oneOf) {
        if (selected === undefined || selected === null) {
          return $attrs.emptyValueDisplay;
        }

        for (let value of oneOf) {
          if (selected === value.const) {
            return value.title;
          }
        }

        return selected;
      };
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

      // Expression "{{ $index }}" has to be replaced by concatenation
      // "' + $index + '" because we use the string in an expression.
      const errorsKey = "'" + toJsonPointer(attrs.path).replace(/{{( )*\$index( )*}}/, '\' + $$index + \'') + "'";
      const validationErrorsTemplate = '<funding-validation-errors errors="errors[' + errorsKey + ']"></funding-validation-errors>';

      let template = '';
      if (attrs.label) {
        template += '<label class="control-label">{{' + attrs.label + '}} ' + validationErrorsTemplate + '</label> ';
      }

      let displayValueExpression;
      if (attrs.type === 'checkbox') {
        displayValueExpression = `showCheckbox(${attrs.value})`;
      } else if (attrs.type === 'checklist') {
        displayValueExpression = 'showChecklist(' + attrs.value + ', ' + attrs.optionsOneOf + ')';
      } else if (attrs.type === 'select') {
        displayValueExpression = 'showSelect(' + attrs.value + ', ' + attrs.optionsOneOf + ')';
      } else {
        displayValueExpression = '(null === ' + attrs.value + ' || "" === ' + attrs.value + ') ? $ctrl.emptyValueDisplay : ' + attrs.value;
      }

      let editElement;
      if (attrs.type === 'textarea') {
        editElement = angular.element('<pre>{{ ' + displayValueExpression + ' }}</pre>');
      } else {
        editElement = angular.element('<span>{{ ' + displayValueExpression + ' }}</span>');
        if (attrs.optionsOneOf) {
          editElement.attr('e-ng-options', 'o.const as o.title for o in ' + attrs.optionsOneOf);
        }
      }

      editElement.attr('ng-show', '$ctrl.editAllowed');
      editElement.attr('editable-' + attrs.type, attrs.value);
      editElement.attr('e-name', attrs.path);
      editElement.attr('e-form', '{{$ctrl.formName}}');
      editElement.attr('onshow', 'onStartEdit(this)');
      editElement.attr('onbeforesave', 'onBeforeSave(this)');
      editElement.attr('onaftersave', 'onAfterSave(this)');
      editElement.attr('oncancel', 'onCancelEdit(this)');
      editElement.attr('onhide', 'onEditFinished(this)');
      for (let [key, value] of Object.entries(attrs)) {
        if (['path', 'type', 'value', 'formName', 'optionsOneOf'].includes(key) ||
          key.startsWith('$')) {
          continue;
        }
        editElement.attr(_4.kebabCase(key), value);
      }
      template += editElement[0].outerHTML;

      let viewOnlyElement;
      if (attrs.type === 'textarea') {
        viewOnlyElement = angular.element('<pre>{{ ' + displayValueExpression + ' }}</pre>');
      } else {
        viewOnlyElement = angular.element('<span>{{ ' + displayValueExpression + ' }}</span>');
      }
      viewOnlyElement.attr('ng-show', '!$ctrl.editAllowed');
      template += viewOnlyElement[0].outerHTML;

      if (!attrs.label) {
        template += ' ' + validationErrorsTemplate;
      }
      template = '<span ng-class="{\'has-warning\': errors[' + errorsKey + '].length}">' + template + '</span>';

      return template;
    },
  };
}]);
