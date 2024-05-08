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

const Ajv = window.ajv7;
const ajv = new Ajv();

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

function fixHeight(selector) {
  // Make element filling remaining window height when main scrollbar is at the top.
  const $element = CRM.$(selector);
  const height = CRM.$(window).height() - $element.offset().top;
  $element.height(Math.floor(height));
}

function fixHeights() {
  CRM.$('.funding-resize-height:visible').each((index, element) => fixHeight(element));
}

CRM.$(document).ready(() => {
  CRM.$(window).on('resize', fixHeights);
  window.setTimeout(fixHeights, 1200);
});
