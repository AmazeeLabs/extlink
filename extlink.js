(function ($, Drupal, drupalSettings) {

  Drupal.extlink = Drupal.extlink || {};

  Drupal.extlink.attach = function (context, drupalSettings) {
    if (!drupalSettings.hasOwnProperty('extlink')) {
      return;
    }

    // Strip the host name down, removing ports, subdomains, or www.
    var pattern = /^(([^\/:]+?\.)*)([^\.:]{4,})((\.[a-z]{1,4})*)(:[0-9]{1,5})?$/;
    var host = window.location.host.replace(pattern, '$3$4');
    var subdomain = window.location.host.replace(pattern, '$1');

    // Determine what subdomains are considered internal.
    var subdomains;
    if (drupalSettings.extlink.extSubdomains) {
      subdomains = "([^/]*\\.)?";
    }
    else if (subdomain == 'www.' || subdomain == '') {
      subdomains = "(www\\.)?";
    }
    else {
      subdomains = subdomain.replace(".", "\\.");
    }

    // Build regular expressions that define an internal link.
    var internal_link = new RegExp("^https?://" + subdomains + host, "i");

    // Extra internal link matching.
    var extInclude = false;
    if (drupalSettings.extlink.extInclude) {
      extInclude = new RegExp(drupalSettings.extlink.extInclude.replace(/\\/, '\\'), "i");
    }

    // Extra external link matching.
    var extExclude = false;
    if (drupalSettings.extlink.extExclude) {
      extExclude = new RegExp(drupalSettings.extlink.extExclude.replace(/\\/, '\\'), "i");
    }

    // Extra external link CSS selector exclusion.
    var extCssExclude = false;
    if (drupalSettings.extlink.extCssExclude) {
      extCssExclude = drupalSettings.extlink.extCssExclude;
    }

    // Extra external link CSS selector explicit.
    var extCssExplicit = false;
    if (drupalSettings.extlink.extCssExplicit) {
      extCssExplicit = drupalSettings.extlink.extCssExplicit;
    }

    // Find all links which are NOT internal and begin with http as opposed
    // to ftp://, javascript:, etc. other kinds of links.
    // When operating on the 'this' variable, the host has been appended to
    // all links by the browser, even local ones.
    // In jQuery 1.1 and higher, we'd use a filter method here, but it is not
    // available in jQuery 1.0 (Drupal 5 default).
    var external_links = new Array();
    var mailto_links = new Array();
    $("a:not(." + drupalSettings.extlink.extClass + ", ." + drupalSettings.extlink.mailtoClass + "), area:not(." + drupalSettings.extlink.extClass + ", ." + drupalSettings.extlink.mailtoClass + ")", context).each(function(el) {
      try {
        var url = this.href.toLowerCase();
        if (url.indexOf('http') == 0
          && ((!url.match(internal_link) && !(extExclude && url.match(extExclude))) || (extInclude && url.match(extInclude)))
          && !(extCssExclude && $(this).parents(extCssExclude).length > 0)
          && !(extCssExplicit && $(this).parents(extCssExplicit).length < 1)) {
          external_links.push(this);
        }
        // Do not include area tags with begin with mailto: (this prohibits
        // icons from being added to image-maps).
        else if (this.tagName != 'AREA'
          && url.indexOf('mailto:') == 0
          && !(extCssExclude && $(this).parents(extCssExclude).length > 0)
          && !(extCssExplicit && $(this).parents(extCssExplicit).length < 1)) {
          mailto_links.push(this);
        }
      }
        // IE7 throws errors often when dealing with irregular links, such as:
        // <a href="node/10"></a> Empty tags.
        // <a href="http://user:pass@example.com">example</a> User:pass syntax.
      catch (error) {
        return false;
      }
    });

    if (drupalSettings.extlink.extClass) {
      Drupal.extlink.applyClassAndSpan(external_links, drupalSettings.extlink.extClass);
    }

    if (drupalSettings.extlink.mailtoClass) {
      Drupal.extlink.applyClassAndSpan(mailto_links, drupalSettings.extlink.mailtoClass);
    }

    if (drupalSettings.extlink.extTarget) {
      // Apply the target attribute to all links.
      $(external_links).attr('target', drupalSettings.extlink.extTarget);
    }

    Drupal.extlink = Drupal.extlink || {};

    // Set up default click function for the external links popup. This should be
    // overridden by modules wanting to alter the popup.
    Drupal.extlink.popupClickHandler = Drupal.extlink.popupClickHandler || function() {
      if (drupalSettings.extlink.extAlert) {
        return confirm(drupalSettings.extlink.extAlertText);
      }
    }

    $(external_links).click(function(e) {
      return Drupal.extlink.popupClickHandler(e);
    });
  };

  /**
   * Apply a class and a trailing <span> to all links not containing images.
   *
   * @param links
   *   An array of DOM elements representing the links.
   * @param class_name
   *   The class to apply to the links.
   */
  Drupal.extlink.applyClassAndSpan = function (links, class_name) {
    var $links_to_process;
    if (drupalSettings.extlink.extImgClass){
      $links_to_process = $(links);
    }
    else {
      var links_with_images = $(links).find('img').parents('a');
      $links_to_process = $(links).not(links_with_images);
    }
    $links_to_process.addClass(class_name);
    var i;
    var length = $links_to_process.length;
    for (i = 0; i < length; i++) {
      var $link = $($links_to_process[i]);
      if ($link.css('display') == 'inline' || $link.css('display') == 'inline-block') {
        if (class_name == drupalSettings.extlink.mailtoClass) {
          $link.append('<span class="' + class_name + '"><span class="element-invisible"> ' + drupalSettings.extlink.mailtoLabel + '</span></span>');
        }
        else {
          $link.append('<span class="' + class_name + '"><span class="element-invisible"> ' + drupalSettings.extlink.extLabel + '</span></span>');
        }
      }
    }
  };

  Drupal.behaviors.extlink = Drupal.behaviors.extlink || {};
  Drupal.behaviors.extlink.attach = function (context, drupalSettings) {
    // Backwards compatibility, for the benefit of modules overriding extlink
    // functionality by defining an "extlinkAttach" global function.
    if (typeof extlinkAttach === 'function') {
      extlinkAttach(context);
    }
    else {
      Drupal.extlink.attach(context, drupalSettings);
    }
  };

})(jQuery, Drupal, drupalSettings);
