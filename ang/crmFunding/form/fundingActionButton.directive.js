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

fundingModule.directive('fundingActionButton', ['$compile', function($compile) {
  return {
    restrict: 'E',
    transclude: true,
    scope: true,
    bindToController: {
      'action': '@',
      'label': '@',
      'withComment': '=?',
    },
    controllerAs: '$ctrl',
    controller: function () {
        this.withComment = true;
    },
    compile: function(element, attrs) {
      // copy attributes to button element
      const button = angular.element(element[0].querySelector('button'));
      for (let attr of element[0].attributes) {
        if (attr.name !== 'action' && attr.name !== 'label' && attr.name !== 'with-comment') {
          button.attr(attr.name, attr.value);
          // avoid handling of attributes such as crm-icon on custom button directive
          _4.unset(attrs, _4.camelCase(attr.name));
          // element.removeAttr(attr.name) has no impact at this point, so we do
          // it in link function below
        }
      }

      return function (scope, element, attrs, controller, transcludeFn) {
        for (let attr of element[0].attributes) {
          if (attr.name !== 'action' && attr.name !== 'label' && attr.name !== 'with-comment') {
            element.removeAttr(attr.name);
          }
        }

        transcludeFn(function (clone) {
          if (clone[0]) {
            element.find('button').find('span.funding-label').html(clone);
            if (!controller.label) {
              const cloneText = clone.text();
              const expressionMatch = cloneText.match(/^\{\{(.+)\}\}$/);
              if (expressionMatch && expressionMatch[1]) {
                controller.label = scope.$eval(expressionMatch[1]);
              }
              else {
                controller.label = cloneText;
              }
            }
          }
        });
      };
    },
    templateUrl: '~/crmFunding/form/fundingActionButton.template.html',
  };
}]);
