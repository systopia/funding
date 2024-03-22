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
          scope.propertySchema = propertySchemaPath === '' ? jsonSchema : _4.get(jsonSchema, propertySchemaPath);

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

        if (typeof type !== 'string') {
          element.html('');

          throw new Error(`Unknown JSON schema type ${propertySchema.type}`);
        }

        const uiSchemaType = getUiSchemaOption('type');
        if (type === 'null' || uiSchemaType === 'hidden' || uiSchemaType === 'submit') {
          element.html('');

          return;
        }

        scope.path = getPath();

        const uiSchemaFormat = getUiSchemaOption('format');
        if (uiSchemaFormat === 'file') {
          element.html($compile('<funding-jf-control-file></funding-jf-control-file>')(scope));

          return;
        }

        scope.required = scope.objectSchema.required instanceof Array &&
          scope.objectSchema.required.includes(scope.propertyName);

        let inputType;

        if (type === 'array') {
          if (propertySchema.uniqueItems && propertySchema.items.oneOf) {
            inputType = 'checklist';
          } else {
            element.html($compile('<funding-jf-control-array></funding-jf-control-array>')(scope));

            return;
          }
        } else if (['string', 'number', 'integer', 'boolean'].includes(type) && propertySchema.oneOf) {
          if (uiSchemaFormat === 'radio') {
            // @todo Should be 'radiolist', though when editing the resulting value is always undefined...
            inputType = 'select';
          } else {
            inputType = 'select';
          }
        } else if (type === 'integer' || type === 'number') {
          inputType = 'number';
        } else if (type === 'string') {
          if (propertySchema.format === 'date') {
            inputType = 'date';
          } else if (propertySchema.format === 'date-time') {
            inputType = 'datetime-local';
          } else if (propertySchema.format === 'email' || propertySchema.format === 'idn-email') {
            inputType = 'email';
          } else if (propertySchema.format === 'uri' || propertySchema.format === 'uri-reference') {
            inputType = 'url';
          } else {
            inputType = getUiSchemaOption('multi') ? 'textarea' : 'text';
          }
        } else if (type === 'boolean') {
          inputType = 'checkbox';
        } else {
          element.html('');

          throw new Error(`Unknown JSON schema type ${type}`);
        }

        if (propertySchema.readOnly || propertySchema.$calculate || uiSchema.readonly) {
          scope.editable = false;
        }

        const fieldElement = angular.element('<editable-field></editable-field>');
        fieldElement.attr('type', inputType);
        fieldElement.attr('value', 'data.' + scope.path);

        if (!scope.noLabel) {
          if (uiSchema.label === null || uiSchema.label === undefined) {
            fieldElement.attr('label', "'" + scope.propertyName.charAt(0).toUpperCase() + scope.propertyName.slice(1) + "'");
          } else if (uiSchema.label !== '') {
            fieldElement.attr('label', 'uiSchema.label');
          }
        }

        fieldElement.attr('edit-allowed', 'editable');
        fieldElement.attr('e-ng-required', scope.required);
        fieldElement.attr('error-path-prefix', scope.errorPathPrefix);
        fieldElement.attr('description', 'uiSchema.description');

        if (inputType === 'checklist') {
          fieldElement.attr('options-one-of', 'propertySchema.items.oneOf');
        } else if (propertySchema.oneOf) {
          fieldElement.attr('options-one-of', 'propertySchema.oneOf');
        }

        if (propertySchema.precision !== undefined) {
          fieldElement.attr('e-step', 1 / 10 ** propertySchema.precision);
        }

        // @todo rules
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
