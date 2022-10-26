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

fundingModule.directive('fundingRecipientRelation', ['$compile', function($compile) {
  return {
    restrict: 'E',
    scope: {
      types: '=',
      type: '=',
      properties: '=',
    },
    link: function($scope, elem, attrs) {
      $scope.$watch('type', function (newValue, oldValue) {
        $scope.typeSpecification = $scope.types[newValue];
        if ($scope.typeSpecification) {
          const propertiesElem = angular.element(elem[0].querySelector('.funding-recipient-relation-properties'));
          propertiesElem.html($compile($scope.typeSpecification.template)($scope));
        }
      });
    },
    templateUrl: '~/crmFunding/program/recipients/recipientRelation.template.html',
    controller: function($scope) {
      $scope.ts = CRM.ts('funding');
      $scope.clearProperties = function() {
        $scope.properties = {};
      }
    },
  };
}]);
