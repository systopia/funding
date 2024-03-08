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

fundingModule.directive('fundingClearingProofs', function () {
  return {
    require: { editorCtrl: '^^fundingClearingEditor' },
    scope: true,
    templateUrl: '~/crmFunding/clearing/clearingProofs.template.html',
    link: function (scope, element, attrs, controllers) {
      const editorCtrl = controllers.editorCtrl;
      editorCtrl.$scope.$watch('jsonSchema',
        (jsonSchema) => scope.jsonSchema = jsonSchema);

      editorCtrl.$scope.$watch('proofsUiSchema',
        (proofsUiSchema) => scope.uiSchema = proofsUiSchema);

      editorCtrl.$scope.$watch('data',
        (data) => scope.data = data);

      editorCtrl.$scope.$watch('errors',
        (errors) => scope.errors = errors);

      editorCtrl.$scope.$watch('inserted',
        (inserted) => scope.inserted = inserted);

      scope.isEditAllowed = editorCtrl.$scope.isEditAllowed;
      scope.onStartEdit = editorCtrl.$scope.onStartEdit;
      scope.onBeforeSave = editorCtrl.$scope.onBeforeSave;
      scope.onAfterSave = editorCtrl.$scope.onAfterSave;
      scope.onCancelEdit = editorCtrl.$scope.onCancelEdit;
      scope.onEditFinished = editorCtrl.$scope.onEditFinished;
      scope.onCancelEdit = editorCtrl.$scope.onCancelEdit;
      scope.addTo = editorCtrl.$scope.addTo;
      scope.cancelInsertAt = editorCtrl.$scope.cancelInsertAt;
      scope.removeAt = editorCtrl.$scope.removeAt;
    },
    controller: function () {},
  };
});
