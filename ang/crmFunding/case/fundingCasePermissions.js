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
  $routeProvider.when('/funding/case/:fundingCaseId/permissions', {
    controller: 'fundingCasePermissionsCtrl',
    controllerAs: '$ctrl',
    templateUrl: '~/crmFunding/case/fundingCasePermissions.html',
    resolve: {
      types: ['fundingCaseContactRelationTypeService', function(fundingCaseContactRelationTypeService) {
        return fundingCaseContactRelationTypeService.getAll();
      }],
      relations: ['$route', 'fundingCaseContactRelationService', function($route, fundingCaseContactRelationService) {
        return fundingCaseContactRelationService.getAll($route.current.params.fundingCaseId);
      }],
      possiblePermissions: ['fundingCaseContactRelationService', function(fundingCaseContactRelationService) {
        return fundingCaseContactRelationService.getPossiblePermissions();
      }],
    }
  });
}]);

fundingModule.controller('fundingCasePermissionsCtrl', [
  '$scope', '$routeParams', 'fundingCaseContactRelationService', 'crmStatus', 'types', 'relations', 'possiblePermissions',
  function($scope, $routeParams, fundingCaseContactRelationService, crmStatus, types, relations, possiblePermissions) {
    $scope.ts = CRM.ts('funding');
    const fundingCaseId = $routeParams.fundingCaseId;

    $scope.relations = relations;
    $scope.types = types;
    $scope.possiblePermissions = possiblePermissions;

    $scope.add = function () {
      $scope.relations.push({funding_case_id: fundingCaseId, properties: {}, permissions: []});
    };

    $scope.remove = function (index) {
      $scope.relations.splice(index, 1);
    };

    $scope.save = function () {
      return crmStatus(
        {},
        fundingCaseContactRelationService.replaceAll(fundingCaseId, $scope.relations).then(function (relations) {
          $scope.relations = relations;
        })
      );
    };
  }
]);
