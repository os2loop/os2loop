/**
 * @file
 * View AJAX refresh when certain flags are selected.
 */

(function ($, Drupal, drupalSettings) {

  Drupal.viewsFlagRefresh = {};
  Drupal.viewsFlagRefresh.exposedFormAjax = {};

  /**
   * Process view instance.
   */
  Drupal.viewsFlagRefresh.processViewInstance = function (view, i, flag) {
    var settings = drupalSettings.viewsFlagRefresh || {};
    if (settings && settings.flags && settings.flags[flag]) {
      var name = view.settings.view_name;
      var display = view.settings.view_display_id;
      var ajax = Drupal.viewsFlagRefresh.exposedFormAjax[i] || view.refreshViewAjax || {};

      // Process view ajax.
      settings = settings.flags[flag];
      if (ajax && settings[name] && settings[name][display]) {
        Drupal.viewsFlagRefresh.processAjax(ajax);
      }
    }
  };

  /**
   * Process ajax settings.
   */
  Drupal.viewsFlagRefresh.processAjax = function (ajax) {
    // Trigger event for exposed form submit.
    if (ajax.$form && ajax.element && ajax.event) {
      $(ajax.element).trigger(ajax.event);
    }
    // Execute view refresh ajax.
    else if (typeof ajax.execute === 'function') {
      ajax.execute();
    }
  };

  Drupal.behaviors.viewsFlagRefresh = {};
  Drupal.behaviors.viewsFlagRefresh.attach = function () {
    // Store information about exposed forms ajax,
    // because it can be inaccessible.
    if (Drupal.views && Drupal.views.instances) {
      var viewsInstances = Drupal.views.instances;
      Object.keys(viewsInstances || {}).forEach(function (i) {
        if (viewsInstances[i].exposedFormAjax && viewsInstances[i].exposedFormAjax[0]) {
          Drupal.viewsFlagRefresh.exposedFormAjax[i] = viewsInstances[i].exposedFormAjax[0];
        }
      });
    }
  };

  /**
   * Definition of viewsFlagRefresh AJAX command.
   */
  Drupal.AjaxCommands.prototype.viewsFlagRefresh = function (ajax, response, status) {
    if (status === 'success' && Drupal.views && Drupal.views.instances) {
      var viewsInstances = Drupal.views.instances;
      Object.keys(viewsInstances || {}).forEach(function (i) {
        Drupal.viewsFlagRefresh.processViewInstance(viewsInstances[i], i, response.flag);
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
