(function($) {
  'use strict';
  $(function() {
    var $sidebar = $('.sidebar-offcanvas');
    $('[data-toggle="offcanvas"]').on("click", function() {
      var expanded = $(this).attr('aria-expanded') === 'true';
      $(this).attr('aria-expanded', expanded ? 'false' : 'true');
      $sidebar.toggleClass('active', !expanded);
    });
  });
})(jQuery);
