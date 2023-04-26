'use strict';

const fundingModule = angular.module('crmFunding', CRM.angRequires('crmFunding'));

// Configure xeditable
fundingModule.run(['editableOptions', 'editableThemes', function(editableOptions, editableThemes) {
  editableThemes.bs3.inputClass = 'input-sm';
  editableThemes.bs3.buttonsClass = 'btn-sm';
  // CiviCRM's bootstrap uses the same color for btn-primary and btn-default, so we use btn-success instead
  editableThemes.bs3.submitTpl = '<button type="submit" class="btn btn-success"><span></span></button>';
  editableOptions.theme = 'bs3';
  editableOptions.blurElem = 'ignore';
  editableOptions.icon_set = 'font-awesome';
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
