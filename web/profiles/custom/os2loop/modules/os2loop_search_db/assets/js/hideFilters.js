/**
 * Hide search filters if the search yields no result.
 */
(function (Drupal, drupalSettings) {
  'use strict';
  Drupal.behaviors.hideFilters = {
    attach: function (context, settings) {
      let noResult = document.getElementById('js-no-result');
      if (noResult) {
        document.getElementById('js-search-filters').style.display = 'none';
      }
    }
  };

})(Drupal, drupalSettings);
