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

fundingModule.directive('fundingJfControl', ['$compile', function($compile) {
  return {
    restrict: 'E',
    scope: false,
    template: '',
    link: function (scope, element) {
      let jsonSchema;
      let uiSchema;

      let dataUnwatch;

      function update() {
        if (jsonSchema !== undefined && uiSchema !== undefined) {
          const propertySchemaPath = uiSchema.scope.substring(2)
            .replaceAll('/', '.')
            .replaceAll(/\.([0-9]+)(\.|$)/g, '[$1]$2');
          const objectSchemaPath = uiSchema.scope.replace(/\/properties\/[^\/]+$/, '')
            .substring(2)
            .replaceAll('/', '.')
            .replaceAll(/\.([0-9]+)(\.|$)/g, '[$1]$2');

          scope.propertyName = uiSchema.scope.substring(uiSchema.scope.lastIndexOf('/') + 1);
          scope.objectSchema = objectSchemaPath === '' ? jsonSchema : _4.get(jsonSchema, objectSchemaPath);
          scope.propertySchema = _4.get(jsonSchema, propertySchemaPath);

          if (!dataUnwatch) {
            dataUnwatch = scope.$watch('data', function fallbackToDefault(data) {
              if (typeof data !== 'object' || scope.propertySchema.default === undefined) {
                return;
              }

              if (_4.get(data, scope.path) === undefined) {
                _4.set(data, scope.path, _4.cloneDeep(scope.propertySchema.default));
              }
            });
          }
        }
      }

      scope.$watch('jsonSchema', function(value) {
        if (value !== undefined) {
          jsonSchema = value;
          update();
        }
      });

      scope.$watch('uiSchema', function(value) {
        if (value !== undefined) {
          uiSchema = value;
          update();
        }
      });

      function getPath() {
        return scope.uiSchema.scope
          .replaceAll('/properties/', '.')
          .replaceAll('/', '.')
          .replaceAll(/\.([0-9]+)(\.|$)/g, '[$1]$2')
          .substring(2);
      }

      function getUiSchemaOption(key, defaultValue = undefined) {
        if (Object.hasOwn(scope.uiSchema.options || {}, key)) {
          return uiSchema.options[key];
        }

        return defaultValue;
      }

      scope.$watch('propertySchema', function (propertySchema) {
        if (propertySchema === undefined) {
          element.html('');

          return;
        }

        let type = propertySchema.type;
        if (type instanceof Array) {
          type = _4.find(type, (value) => value !== 'null');
        }

        if (typeof type !== 'string' || type === '' || type === 'null') {
          console.error('Unknown JSON schema type', type);
          element.html('');

          return;
        }

        scope.path = getPath();

        const uiSchemaType = getUiSchemaOption('type');
        if (uiSchemaType === 'hidden' || uiSchemaType === 'value') {
          element.html('');

          return;
        }

        if (getUiSchemaOption('format') === 'file') {
          element.html($compile('<funding-jf-control-file></funding-jf-control-file>')(scope));

          return;
        }

        scope.required = scope.objectSchema.required instanceof Array &&
          scope.objectSchema.required.includes(scope.propertyName);

        // @todo checklist, textarea, select, time, radios, url, email

        if (type === 'array') {
          element.html($compile('<funding-jf-control-array></funding-jf-control-array>')(scope));

          return;
        }

        let inputType;
        if (type === 'integer' || type === 'number') {
          inputType = 'number';
        } else if (type === 'string') {
          inputType = 'text';
        } else if (type === 'boolean') {
          inputType = 'checkBox';
        } else if (type === 'date') {
          inputType = 'date';
        } else {
          console.error('Unknown JSON schema type', type);
          element.html('');

          return;
        }

        if (propertySchema.readOnly || propertySchema.$calculate || uiSchema.readonly) {
          scope.editable = false;
        }

        let label = null;
        if (!scope.noLabel) {
          if (uiSchema.label === null || uiSchema.label === undefined) {
            label = scope.propertyName.charAt(0).toUpperCase() + scope.propertyName.slice(1);
          } else {
            label = uiSchema.label;
          }
        }

        const fieldElement = angular.element('<editable-field></editable-field>');
        fieldElement.attr('type', inputType);
        fieldElement.attr('value', 'data.' + scope.path);
        fieldElement.attr('label', label);
        fieldElement.attr('edit-allowed', 'editable');
        fieldElement.attr('e-ng-required', scope.required);
        fieldElement.attr('error-path-prefix', scope.errorPathPrefix);

        if (propertySchema.precision !== undefined) {
          fieldElement.attr('e-step', 1 / (10 ** propertySchema.precision));
        }

        // @todo minimum, maximum, length, pattern

        const template = fieldElement[0].outerHTML;
        element.html($compile(template)(scope));
      });
    },
    controller: ['$scope', function ($scope) {
      // For editable table rows.
      $scope.formName = $scope.$parent.$eval('formName');
      if ($scope.formName) {
        $scope[$scope.formName] = $scope.$parent.$eval($scope.formName);
      }
    }],
  };
}]);
