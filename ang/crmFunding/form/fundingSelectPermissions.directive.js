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

fundingModule.directive('fundingSelectPermissions', [function() {
  return {
    restrict: 'E',
    scope: {
      permissions: '=', // Permissions of relation.
      possiblePermissions: '=', // Permissions from which can be chosen.
    },
    controller: function($scope) {
      $scope.ts = CRM.ts('funding');
    },
    controllerAs: '$ctrl',
    templateUrl: '~/crmFunding/form/fundingSelectPermissions.template.html',
    compile: function(element, attrs) {
      if (attrs.style) {
        const select = angular.element(element[0].querySelector('select'));
        select.attr('style', select.attr('style') + attrs.style);
      }

      return function (scope, element, attr, mCtrl) {
        const ts = CRM.ts('funding');
        scope.$watch('permissions', function (newValue) {
          let containsNonApplication = false;
          let containsApplication = false;

          for (let permission of newValue || []) {
            if (permission.startsWith('application_')) {
              containsApplication = true;
            } else {
              containsNonApplication = true;
            }
          }

          const invalid = containsApplication && containsNonApplication;
          const message = invalid ? ts('Combining application permissions with other ones is not possible.') : '';
          mCtrl.errorMessage = message;
          const select = element[0].querySelector('select');
          // prevent form from being submitted (though select element is hidden)
          select.setCustomValidity(message);
        });
      };
    },
    /*link: function (scope, element, attr, mCtrl) {
      const ts = CRM.ts('funding');
      scope.$watch('permissions', function (newValue) {
        let containsNonApplication = false;
        let containsApplication = false;

        for (let permission of newValue || []) {
          if (permission.startsWith('application_')) {
            containsApplication = true;
          } else {
            containsNonApplication = true;
          }
        }

        const invalid = containsApplication && containsNonApplication;
        const message = invalid ? ts('Combining application permissions with other ones is not possible.') : '';
        mCtrl.errorMessage = message;
        const select = element[0].querySelector('select');
        // prevent form from being submitted (though select element is hidden)
        select.setCustomValidity(message);
      });
    }*/
  };
}]);
