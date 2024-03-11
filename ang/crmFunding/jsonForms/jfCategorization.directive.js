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

fundingModule.directive('fundingJfCategorization', [function() {
  let count = 0;

  return {
    restrict: 'E',
    scope: false,
    templateUrl: '~/crmFunding/jsonForms/jfCategorization.template.html',
    controller: ['$scope', function($scope) {
      $scope.count = ++count;
      $scope.activeTab = 0;

      $scope.setActiveTab = function (index) {
        $scope.activeTab = index;
      };
    }],
  };
}]);
