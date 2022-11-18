const fundingModule = angular.module('crmFunding', CRM.angRequires('crmFunding'));

// Configure xeditable
fundingModule.run(['editableOptions', 'editableThemes', function(editableOptions, editableThemes) {
  editableThemes.bs3.inputClass = 'input-sm';
  editableThemes.bs3.buttonsClass = 'btn-sm';
  editableOptions.theme = 'bs3';
  editableOptions.blurElem = 'ignore';
}]);
