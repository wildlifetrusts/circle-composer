/**
 * @file
 * Handle responsive tables.
 */
(function () {
  'use strict';

  /**
   * Behaviour to add data attributes to responsive tables with headers.
   *
   * @type {{attach: Drupal.behaviors.wildlifeTrustResponsiveTables.attach}}
   */
  Drupal.behaviors.wildlifeTrustResponsiveTables = {
    attach: function () {
      var tables = document.querySelectorAll('.table--responsive');

      // Only continue if there are responsive tables.
      if (!tables) { return; }

      // Loop through all tables on the page.
      [].forEach.call(tables, function (table) {
        // Get thead and tbody elements (th/tr elements outside of
        // these can be ignored as they're not hidden by CSS).
        var thead = table.querySelector('thead');
        var tbody = table.querySelector('tbody');

        // Only continue if there are thead and tbody elements.
        if (!thead || !tbody) { return; }

        // Get all th elements within the thead element and all
        // tr elements within the tbody element.
        var thAll = thead.querySelectorAll('th');
        var trAll = tbody.querySelectorAll('tr');

        // Only continue if there are th and tr elements.
        if (!thAll || !trAll) { return; }

        // Define array to be used to store the text for each header.
        var headers = [];

        // Loop through all th elements in the table and push the text
        // content of each to the headers array.
        [].forEach.call(thAll, function (th) {
          headers.push(th.textContent);
        });

        // Loop through all rows of the table so as to then loop through all
        // td elements.
        [].forEach.call(trAll, function (tr) {
          var tdAll = tr.querySelectorAll('td');

          // Only continue if there are td elements.
          if (!tdAll) { return; }

          // Loop through all td elements in the row and set their
          // 'data-header' attribute to the header text content stored
          // in the same index of the headers array.
          [].forEach.call(tdAll, function (td, index) {
            td.setAttribute('data-header', headers[index]);
          });
        });
      });
    }
  };

})();
