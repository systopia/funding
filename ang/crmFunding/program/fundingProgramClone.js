/*
 * Copyright (C) 2026 SYSTOPIA GmbH
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
  $routeProvider.when('/funding/program/:fundingProgramId/clone', {
    template: '<div class="crm-loading-element"></div>',
    controller: 'FundingProgramCloneCtrl'
  });
}]);

fundingModule.controller('FundingProgramCloneCtrl', ['$scope', '$routeParams', 'fundingProgramService', 'crmStatus',
  function($scope, $routeParams, fundingProgramService, crmStatus) {
    var id = parseInt($routeParams.fundingProgramId);

    crmStatus({start: CRM.ts('funding')('Cloning...')}, fundingProgramService.clone(id)
      .then(function(newProgram) {
        window.location.href = CRM.url('civicrm/funding/program/edit') + '#?FundingProgram1=' + newProgram.id;
      })
    );
  }
]);
