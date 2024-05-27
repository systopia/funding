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

fundingModule.directive('fundingJfControlFile', [function() {
  return {
    restrict: 'E',
    require: { jfControlCtrl: '^^fundingJfControl' },
    scope: false,
    controllerAs: '$ctrl',
    templateUrl: '~/crmFunding/jsonForms/jfControlFile.template.html',
    controller: ['$scope', function($scope) {
      // read only for now.
      $scope.ts = CRM.ts('funding');

      const ctrl = this;
      this.getUri = function () {
        if ($scope.propertySchema.format === 'uri') {
          return '' === $scope.path ? $scope.data : _4.get($scope.data, $scope.path);
        }

        return null;
      };

      this.getFileName = function () {
        const uri = ctrl.getUri();

        return uri ? decodeURI(uri.substring(uri.lastIndexOf('/') + 1)) : null;
      };
    }],
  };
}]);
