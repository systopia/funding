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

fundingModule.config(['$routeProvider', function($routeProvider) {
  $routeProvider.when('/funding/program/:fundingProgramId/new-case-permissions', {
    controller: 'fundingNewCasePermissionsCtrl',
    controllerAs: '$ctrl',
    templateUrl: '~/crmFunding/program/fundingNewCasePermissions.html',
    resolve: {
      types: ['fundingCaseContactRelationPropertiesFactoryTypeService', function(fundingCaseContactRelationPropertiesFactoryTypeService) {
        return fundingCaseContactRelationPropertiesFactoryTypeService.getAll();
      }],
      newCasePermissionsList: ['$route', 'fundingNewCasePermissionsService', function($route, fundingNewCasePermissionsService) {
        return fundingNewCasePermissionsService.getAll($route.current.params.fundingProgramId);
      }],
      possiblePermissions: ['fundingNewCasePermissionsService', function(fundingNewCasePermissionsService) {
        return fundingNewCasePermissionsService.getPossiblePermissions();
      }],
    }
  });
}]);

fundingModule.controller('fundingNewCasePermissionsCtrl', [
  '$scope', '$routeParams', 'fundingNewCasePermissionsService', 'crmStatus', 'types', 'newCasePermissionsList', 'possiblePermissions',
  function($scope, $routeParams, fundingNewCasePermissionsService, crmStatus, types, newCasePermissionsList, possiblePermissions) {
    $scope.ts = CRM.ts('funding');
    const fundingProgramId = $routeParams.fundingProgramId;

    $scope.newCasePermissionsList = newCasePermissionsList;
    $scope.types = types;
    $scope.possiblePermissions = possiblePermissions;

    $scope.add = function () {
      $scope.newCasePermissionsList.push({funding_program_id: fundingProgramId, properties: {}, permissions: []});
    };

    $scope.remove = function (index) {
      $scope.newCasePermissionsList.splice(index, 1);
    };

    $scope.save = function () {
      return crmStatus(
        {},
          fundingNewCasePermissionsService.replaceAll(fundingProgramId, $scope.newCasePermissionsList).then(function (newCasePermissionsList) {
          $scope.newCasePermissionsList = newCasePermissionsList;
        })
      );
    };
  }
]);
