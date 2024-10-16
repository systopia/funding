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

fundingModule.directive('fundingNotificationContacts', function() {
  return {
    restrict: 'E',
    scope: {
      fundingCase: '=',
      editAllowed: '<',
    },
    templateUrl: '~/crmFunding/case/notificationContacts.template.html',
    controller: ['$scope', 'crmStatus', 'fundingCaseService', 'fundingContactService',
      function($scope, crmStatus, fundingCaseService, fundingContactService) {
        const ts = $scope.ts = CRM.ts('funding');

        $scope.setContacts = (ids) => {
          fundingCaseService.setNotificationContacts($scope.fundingCase.id, ids);
        };

        let selected = [];
        $scope.contacts = [];

        function updateDisplayValue() {
          $scope.contactNames = selected.map((item) => item.label).join('; ') || ts('empty');
        }

        if ($scope.fundingCase.notification_contact_ids.length > 0) {
          fundingContactService.autocompleteByIds($scope.fundingCase.notification_contact_ids)
            .then(function (result) {
              selected = $scope.contacts = result;
              $scope.fundingCase.notification_contact_ids = result.map((item) => item.id);
              updateDisplayValue();
            });
        }
        else {
          updateDisplayValue();
        }

        $scope.refreshContacts = (search) => {
          fundingContactService.autocomplete(search).then(function(result) {
            $scope.contacts = result;
          });
        };

        $scope.onSelect = (selectedItem) => {
          selected.push(selectedItem);
          updateDisplayValue();
        };

        $scope.onRemove = (removedItem) => {
          _4.remove(selected, (item) => item.id === removedItem.id);
          updateDisplayValue();
        };
      },
    ],
  };
});
