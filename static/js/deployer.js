(function($) {
  $(document).ready(function() {
    $('.js-deployer').on('click', function(event) {
      event.preventDefault();
      var $container = $(this)
        .closest('.netlify-deploybot-actions')
        .addClass('netlify-deploybot--loading');
    });
  });
})(jQuery);
