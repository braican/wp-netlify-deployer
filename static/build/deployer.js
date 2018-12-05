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


    _createClass(Deployer, [{
      key: "init",
      value: function init() {
        var _this = this;

        this.$trigger.on('click', function (event) {
          event.preventDefault();

          _this.triggered();

          $.post(ajaxurl, {
            action: 'trigger_deploy',
            build_hook: _this.buildHook
          }).always(_this.completed.bind(_this)).done(function (resp) {
            if (!resp.success) {
              _this.error(resp);
            } else {
              _this.success(resp.data);
            }
          }).fail(function (err) {
            return console.error(err);
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
       * Handle a completed request, success or fail.
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
        this.$trigger.after('<span class="deploy-message deploy-message--error">Something went wrong with the deployment. Check the console for errors, or please try again later.</span>');
      }
      /**
       * Handle a successful POST request.
       *
       * @param {string} msg Success message
       *
       * @return void
       */

    }, {
      key: "success",
      value: function success(msg) {
        this.$trigger.after("<span class=\"deploy-message deploy-message--success\">".concat(msg, "</span>"));
        this.$container.find('.change-count').remove();
      }
    }]);

    return Deployer;
  }();

  $(document).ready(function () {
    $('.js-deployer').each(function (i, btn) {
      return new Deployer(btn);
    });
  });
})(jQuery);