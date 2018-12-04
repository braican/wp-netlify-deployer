(function($) {
  class Deployer {
    constructor(trigger) {
      this.$trigger = $(trigger);
      this.$container = this.$trigger.closest('.js-netlify-deploybot-actions');
      this.$buildHookInput = this.$container.find('#build_hook_url');
      this.buildHook = this.$buildHookInput.val();
      this.buildHookUnsaved = false;

      if (this.buildHook) {
        this.init();
      } else {
        this.$trigger.prop('disabled', true);
      }
    }

    /**
     * Init the event listener.
     *
     * @return void
     */
    init() {
      this.$trigger.on('click', event => {
        event.preventDefault();
        this.triggered();

        $.post(ajaxurl, {
          action: 'trigger_deploy',
          build_hook: this.buildHook
        })
          .always(this.completed.bind(this))
          .done(resp => {
            if (!resp.success) {
              return this.error(resp);
            }

            this.$trigger.after(
              `<span class="deploy-message deploy-message--success">${resp.data}</span>`
            );
          })
          .fail(err => console.error(err));
      });

      this.$buildHookInput.on('keypress', event => {
        if (!this.buildHookUnsaved) {
          this.$trigger
            .hide()
            .after(
              '<span class="unsaved-build-hook">Save this build hook url to deploy to it.</span>'
            );
          this.buildHookUnsaved = true;
          this.$buildHookInput.off('keypress');
        }
      });
    }

    /**
     * The deployment has been triggered, and the POST request sent.
     *
     * @return void
     */
    triggered() {
      if (this.$container.find('.deploy-message').length) {
        this.$container.find('.deploy-message').remove();
      }
      this.$trigger.prop('disabled', true);
      this.$container.addClass('netlify-deploybot--loading');
    }

    /**
     * Handle a completed request, success or fail.
     *
     * @return void
     */
    completed() {
      this.$trigger.prop('disabled', false);
      this.$container.removeClass('netlify-deploybot--loading');
    }

    /**
     * Handle an error from the request.
     *
     * @param {mixed} err Data about the error.
     *
     * @return int 0, since we've got an error.
     */
    error(err) {
      console.error(err);
      this.$trigger.after(
        '<span class="deploy-message deploy-message--error">Something went wrong with the deployment. Check the console for errors, or please try again later.</span>'
      );
      return 0;
    }
  }

  $(document).ready(() => {
    $('.js-deployer').each((i, btn) => new Deployer(btn));
  });
})(jQuery);
