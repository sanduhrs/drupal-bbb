(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.bbbCheckStatusInit = {
    attach: function (context, settings) {
      drupalSettings.bbb.check_status.interval = setInterval(function () {
        $.getJSON(drupalSettings.bbb.check_status.url, function (data) {
          if (data.running === true) {
            location.reload();
          }
        }).fail(
          clearInterval(drupalSettings.bbb.check_status.interval)
        );
      }, 5000);
    }
  };
})(jQuery, Drupal, drupalSettings);
