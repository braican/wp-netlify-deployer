"use strict";

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

(function ($) {
  var Deployer =
  /*#__PURE__*/
  function () {
    function Deployer(trigger) {
      _classCallCheck(this, Deployer);

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
      this.buildCheckEndpoint = "".concat(this.netlifyApi).concat(this.siteId, "/deploys?access_token=").concat(this.accessToken);
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


    _createClass(Deployer, [{
      key: "init",
      value: function init() {
        var _this = this;

        this.$trigger.on('click', function (event) {
          event.preventDefault();

          _this.triggered();

          $.post(_this.ajaxurl, {
            action: 'trigger_deploy',
            build_hook: _this.buildHook
          }).done(function (resp) {
            if (!resp.success) {
              _this.completed();

              return _this.error(resp);
            }

            _this.$container.find('.change-count').remove(); // this.buildCheckInterval = setInterval(this.checkBuildStatus.bind(this), 10000);

          }).fail(function (err) {
            _this.completed();

            console.error(err);
          });
        });
        this.$buildHookInput.on('keypress', function () {
          if (!_this.buildHookUnsaved) {
            _this.$trigger.hide().after('<span class="unsaved-build-hook">Save this build hook url to deploy to it.</span>');

            _this.buildHookUnsaved = true;

            _this.$buildHookInput.off('keypress');
          }
        });
      }
      /**
       * The deployment has been triggered, and the POST request sent.
       *
       * @return void
       */

    }, {
      key: "triggered",
      value: function triggered() {
        this.$trigger.prop('disabled', true);
        this.$container.addClass('netlify-deployer--loading').find('.deploy-message').remove();
      }
      /**
       * Handle a completed request to build, and get a final status from Netlify.
       *
       * @return void
       */

    }, {
      key: "completed",
      value: function completed() {
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

    }, {
      key: "error",
      value: function error(err) {
        console.error(err);
        this.$trigger.after('<span class="deploy-trigger-message deploy-trigger-message--error">Something went wrong with the deployment. Check the console for errors, or please try again later.</span>');
      }
      /**
       * Hits the Netlify API to check on the status of a build.
       */

    }, {
      key: "checkBuildStatus",
      value: function checkBuildStatus() {
        var _this2 = this;

        console.log('check the build');
        var $statusDiv = this.$container.find('.js-netlify-build-status').removeClass('build-status--error');
        $.ajax({
          type: 'GET',
          url: this.buildCheckEndpoint
        }).done(function (data) {
          if (!data || !data[0]) {
            return _this2.buildStatusCheckFailure('No data was returned');
          }

          if ($statusDiv.length === 0) {
            $statusDiv = $('<div class="js-netlify-build-status build-status" />').appendTo(_this2.$trigger.parent());
          }

          var state = data[0].state;
          console.log(state);

          if (state === 'ready') {
            _this2.completed();

            clearInterval(_this2.buildCheckInterval);
            $statusDiv.html('The build has succeeded!');
          } else if (state === 'error') {
            _this2.completed();

            clearInterval(_this2.buildCheckInterval);
            $statusDiv.addClass('build-status--error').html("\n              <p>There was a problem with the build.</p>\n              <p>Check the console for error messages, or head to Netify to see what went wrong and to try the build again.</p>\n              ");
            console.error(data[0]);
          } else {
            $statusDiv.html('Building...');
          }
        }).fail(this.buildStatusCheckFailure.bind(this));
      }
      /**
       * Indicates a failure to get the proper build status data. Outputs error messaging.
       *
       * @param {mixed} error The error data.
       *
       * @return void
       */

    }, {
      key: "buildStatusCheckFailure",
      value: function buildStatusCheckFailure(error) {
        clearInterval(this.buildCheckInterval);
        console.error('There was a problem getting the Netlify build status. Please log in to Netlify to check the status of this build.');
        console.error(error);
      }
    }]);

    return Deployer;
  }();

  $(document).ready(function () {
    $('.js-deployer').each(function (i, btn) {
      return new Deployer(btn);
    });
  });
})(window.jQuery);