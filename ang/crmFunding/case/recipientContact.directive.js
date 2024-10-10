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

fundingModule.directive('fundingRecipientContact', function() {
  return {
    restrict: 'E',
    scope: {
      fundingCase: '=',
      editAllowed: '<',
    },
    templateUrl: '~/crmFunding/case/recipientContact.template.html',
    controller: ['$scope', 'crmStatus', 'fundingCaseService', 'fundingContactService',
      function($scope, crmStatus, fundingCaseService, fundingContactService) {
        $scope.ts = CRM.ts('funding');

        $scope.setRecipientContact = (id) => {
          fundingCaseService.setRecipientContact($scope.fundingCase.id, id).then(
            () => $scope.recipientContactName = $scope.possibleContactNames[id]
          );
        };

        $scope.recipientContactId = $scope.fundingCase.recipient_contact_id;
        fundingContactService.get($scope.fundingCase.recipient_contact_id).then(
          (contact) => $scope.recipientContactName = contact.display_name
        );

        $scope.$watch('editAllowed', (editAllowed) => {
          if (editAllowed) {
            fundingCaseService.getPossibleRecipients($scope.fundingCase.id).then((contacts) => {
              $scope.possibleContacts = contacts;
              $scope.possibleContactNames = {};
              contacts.forEach((contact) => $scope.possibleContactNames[contact.id] = contact.name);
            });
          }
        });
      },
    ],
  };
});
