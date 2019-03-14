(function($) {
  class Deployer {
    constructor(trigger) {
      this.$trigger = $(trigger);
      this.$container = this.$trigger.closest('.js-netlify-deployer-actions');
      this.$buildHookInput = this.$container.find('.js-deploy-hook-string');
      this.buildHook = this.$buildHookInput.val();
      this.buildHookUnsaved = false;

      this.ajaxurl = '/wp-admin/admin-ajax.php';

      this.buildCheckInterval = null;

      this.netlifyApi = 'https://api.netlify.com/api/v1/sites/';
      this.siteId = '9d67db46-2f61-401d-b8d5-f6af54bf0193';
      this.accessToken = 'fdc5d8572fe95f66bdcb6cb6a0ea9b8ff373f0fbc48ef418539b25078dbd236c';
      this.buildCheckEndpoint = `${this.netlifyApi}${this.siteId}/deploys?access_token=${
        this.accessToken
      }`;

      this.buildChecks = 0;

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

        $.post(this.ajaxurl, {
          action: 'trigger_deploy',
          build_hook: this.buildHook,
        })
          .done(resp => {
            if (!resp.success) {
              this.completed();
              return this.error(resp);
            }

            this.$container.find('.change-count').remove();
            // this.buildCheckInterval = setInterval(this.checkBuildStatus.bind(this), 10000);
          })
          .fail(err => {
            this.completed();
            console.error(err);
          });
      });

      this.$buildHookInput.on('keypress', () => {
        if (!this.buildHookUnsaved) {
          this.$trigger
            .hide()
            .after(
              '<span class="unsaved-build-hook">Save this build hook url to deploy to it.</span>',
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
     * Handle a completed request to build, and get a final status from Netlify.
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
        '<span class="deploy-trigger-message deploy-trigger-message--error">Something went wrong with the deployment. Check the console for errors, or please try again later.</span>',
      );
    }

    /**
     * Hits the Netlify API to check on the status of a build.
     */
    checkBuildStatus() {
      console.log('check the build');

      let $statusDiv = this.$container
        .find('.js-netlify-build-status')
        .removeClass('build-status--error');

      $.ajax({
        type: 'GET',
        url: this.buildCheckEndpoint,
      })
        .done(data => {
          if (!data || !data[0]) {
            return this.buildStatusCheckFailure('No data was returned');
          }

          if ($statusDiv.length === 0) {
            $statusDiv = $('<div class="js-netlify-build-status build-status" />').appendTo(
              this.$trigger.parent(),
            );
          }

          const state = data[0].state;

          console.log(state);

          if (state === 'ready') {
            this.completed();
            clearInterval(this.buildCheckInterval);
            $statusDiv.html('The build has succeeded!');
          } else if (state === 'error') {
            this.completed();
            clearInterval(this.buildCheckInterval);
            $statusDiv
              .addClass('build-status--error')
              .html(`
              <p>There was a problem with the build.</p>
              <p>Check the console for error messages, or head to Netify to see what went wrong and to try the build again.</p>
              `);
            console.error(data[0]);
          } else {
            $statusDiv
            .html('Building...');
          }
        })
        .fail(this.buildStatusCheckFailure.bind(this));
    }

    /**
     * Indicates a failure to get the proper build status data. Outputs error messaging.
     *
     * @param {mixed} error The error data.
     *
     * @return void
     */
    buildStatusCheckFailure(error) {
      clearInterval(this.buildCheckInterval);
      console.error(
        'There was a problem getting the Netlify build status. Please log in to Netlify to check the status of this build.',
      );
      console.error(error);
    }
  }

  $(document).ready(() => {
    $('.js-deployer').each((i, btn) => new Deployer(btn));
  });
})(window.jQuery);
