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
  $routeProvider.when('/funding/program/:fundingProgramId/permissions', {
    controller: 'fundingProgramPermissionsCtrl',
    controllerAs: '$ctrl',
    templateUrl: '~/crmFunding/program/fundingProgramPermissions.html',
    resolve: {
      types: ['fundingProgramContactRelationTypeService', function(fundingProgramContactRelationTypeService) {
        return fundingProgramContactRelationTypeService.getAll();
      }],
      relations: ['$route', 'fundingProgramContactRelationService', function($route, fundingProgramContactRelationService) {
        return fundingProgramContactRelationService.getAll($route.current.params.fundingProgramId);
      }],
      possiblePermissions: ['fundingProgramContactRelationService', function(fundingProgramContactRelationService) {
        return fundingProgramContactRelationService.getPossiblePermissions();
      }],
    }
  });
}]);

fundingModule.controller('fundingProgramPermissionsCtrl', [
  '$scope', '$routeParams', 'fundingProgramContactRelationService', 'crmStatus', 'types', 'relations', 'possiblePermissions',
  function($scope, $routeParams, fundingProgramContactRelationService, crmStatus, types, relations, possiblePermissions) {
    $scope.ts = CRM.ts('funding');
    const fundingProgramId = $routeParams.fundingProgramId;

    $scope.relations = relations;
    $scope.types = types;
    $scope.possiblePermissions = possiblePermissions;

    $scope.add = function () {
      $scope.relations.push({funding_program_id: fundingProgramId, properties: {}, permissions: []});
    };

    $scope.remove = function (index) {
      $scope.relations.splice(index, 1);
    };

    $scope.save = function () {
      return crmStatus(
        {},
        fundingProgramContactRelationService.replaceAll(fundingProgramId, $scope.relations).then(function (relations) {
          $scope.relations = relations;
        })
      );
    };
  }
]);
