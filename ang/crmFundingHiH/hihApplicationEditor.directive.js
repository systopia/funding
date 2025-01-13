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

fundingHiHModule.directive('fundingHihApplicationEditor', function() {
  return {
    restrict: 'AE',
    scope: false,
    templateUrl: '~/crmFundingHiH/hihApplicationEditor.template.html',
    controllerAs: '$ctrl',
    controller: ['$scope', 'crmApi4', 'crmStatus', function ($scope, crmApi4, crmStatus) {
      $scope.crmUrl = CRM.url;

      crmApi4('Contact', 'get', {
        select: [
          "display_name",
          "address_primary.street_address",
          "address_primary.postal_code",
          "address_primary.city",
          "address_primary.state_province_id:label",
          "email_primary.email",
          "website.url",
          "phone_primary.phone",
          "bank_account.data_parsed",
          "bank_account_reference.reference",
          "projekttraeger.kurzbeschreibung",
        ],
        join: [
          [
            "Website AS website",
            "LEFT",
            ["website.contact_id", "=", "id"],
            ["website.website_type_id:name", "=", '"Work"'],
          ],
          [
            "BankAccount AS bank_account",
            "LEFT",
            ["bank_account.contact_id", "=", "id"],
          ],
          [
            "BankAccountReference AS bank_account_reference",
            "LEFT",
            ["bank_account_reference.ba_id", "=", "bank_account.id"],
            ["bank_account_reference.reference_type_id:name", "=", '"IBAN"'],
          ],
        ],
        where: [["id", "=", $scope.fundingCase.creation_contact_id]],
        groupBy: ["id"]
      }).then(function(contacts) {
        $scope.creationContact = contacts[0];
      });
    }],
  };
});
