'use strict';

const fundingModule = angular.module('crmFunding', CRM.angRequires('crmFunding'));

// Configure xeditable
fundingModule.run(['editableOptions', 'editableThemes', function(editableOptions, editableThemes) {
  editableThemes.bs3.inputClass = 'input-sm';
  editableThemes.bs3.buttonsClass = 'btn-sm';
  editableOptions.theme = 'bs3';
  editableOptions.blurElem = 'ignore';
}]);

let overlayCount = 0;
function enableOverlay() {
  ++overlayCount;
  document.getElementById('funding-overlay').style.display = 'block';
}

function disableOverlay() {
  if (overlayCount > 0) {
    --overlayCount;
  }
  if (overlayCount === 0) {
    document.getElementById('funding-overlay').style.display = 'none';
  }
}

/* jshint unused:false */
function withOverlay(promise) {
  enableOverlay();
  promise.finally(disableOverlay);
}
/* jshint unused:true */
