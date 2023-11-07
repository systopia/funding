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

fundingModule.directive('fundingApplicationReviewer', function() {
  return {
    restrict: 'E',
    scope: {
      contactId: '=',
      possibleContacts: '=',
      label: '=?',
      editAllowed: '=',
      setContact: '<',
    },
    templateUrl: '~/crmFunding/application/applicationReviewer.template.html',
    link: function(scope) {
      scope.$watch('possibleContacts', function (possibleContacts) {
        const contactChoices = [];
        if (possibleContacts) {
          for (const [id, name] of Object.entries(possibleContacts)) {
            contactChoices.push({id: parseInt(id), name});
          }
        }
        scope.contactChoices = contactChoices;
      });
    },
    controller: ['$scope', 'fundingContactService',
      function($scope, fundingContactService) {
        const ts = $scope.ts = CRM.ts('funding');

        $scope.editorOpen = false;

        $scope.onStartEdit = function () {
          $scope.editorOpen = true;
        };

        $scope.onEditFinished = function () {
          $scope.editorOpen = false;
        };

        $scope.loggedInContactId = CRM.config.cid;
        if (null === $scope.contactId) {
          $scope.fallbackContactName = ts('not assigned');
        } else {
          // In case current contact is not allowed, anymore.
          fundingContactService.get($scope.contactId).then(
              (contact) => $scope.fallbackContactName = contact.display_name || ts('Contact %1', {1: $scope.contactId})
          );
        }
      },
    ],
  };
});
