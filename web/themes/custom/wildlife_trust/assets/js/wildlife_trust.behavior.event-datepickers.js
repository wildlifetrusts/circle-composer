/**
 * @file
 * Event search datepicker behaviour.
 *
 */
(function ($, Drupal) {
  Drupal.behaviors.eventDatepickers = {
    attach: function (context, settings) {
      var filterSettings = settings.wildlife_filters;

      if (filterSettings !== 'undefined') {
        var minDate = filterSettings.event_datepicker.today;
        var maxDate = filterSettings.event_datepicker.max_date;
        var $viewFilters = $('#views-exposed-form-location-search-events', context);
        var $dateFrom = $viewFilters.find('input[name="date_from"]');
        var $dateTo = $viewFilters.find('input[name="date_to"]');

        $dateFrom.datepicker({
          dateFormat: 'dd/mm/yy',
          minDate: minDate,
          maxDate: maxDate,
          onSelect: function(dateText) {
            $dateTo.datepicker('option', 'minDate', dateText);
          }
        });

        $dateTo.datepicker({
          dateFormat: 'dd/mm/yy',
          minDate: minDate,
          maxDate: maxDate
        });
      }
    }
  };
})(jQuery, Drupal);
