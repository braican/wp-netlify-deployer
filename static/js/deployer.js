(function($) {
  class Deployer {
    constructor(trigger) {
      this.$trigger = $(trigger);
      this.$container = this.$trigger.closest('.js-netlify-deployer-actions');
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
              this.error(resp);
            } else {
              this.success(resp.data);
            }
          })
          .fail(err => console.error(err));
      });

      this.$buildHookInput.on('keypress', () => {
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
      this.$trigger.prop('disabled', true);
      this.$container
        .addClass('netlify-deployer--loading')
        .find('.deploy-message')
        .remove();
    }

    /**
     * Handle a completed request, success or fail.
     *
     * @return void
     */
    completed() {
      this.$trigger.prop('disabled', false);
      this.$container.removeClass('netlify-deployer--loading');
    }

    /**
     * Handle an error from the request.
     *
     * @param {mixed} err Data about the error.
     *
     * @return void
     */
    error(err) {
      console.error(err);
      this.$trigger.after(
        '<span class="deploy-message deploy-message--error">Something went wrong with the deployment. Check the console for errors, or please try again later.</span>'
      );
    }

    /**
     * Handle a successful POST request.
     *
     * @param {string} msg Success message
     *
     * @return void
     */
    success(msg) {
      this.$trigger.after(`<span class="deploy-message deploy-message--success">${msg}</span>`);
      this.$container.find('.change-count').remove();
    }
  }

  $(document).ready(() => {
    $('.js-deployer').each((i, btn) => new Deployer(btn));
  });
})(jQuery);
