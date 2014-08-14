<?php

/**
 * @file
 * Contains \Drupal\extlink\Form\ExtlinkAdminSettingsForm.
 */

namespace Drupal\extlink\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Displays the extlink settings form.
 */
class ExtlinkAdminSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'extlink_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = \Drupal::config('extlink.settings');

    $form['extlink_class'] = array(
      '#type' => 'checkbox',
      '#title' => t('Place an icon next to external links.'),
      '#return_value' => 'ext',
      '#default_value' => $config->get('extlink_class'),
      '#description' => t('Places an !icon icon next to external links.', array('!icon' => _theme('image', array('uri' => drupal_get_path('module', 'extlink') . '/extlink.png', 'alt' => t('External Links icon'))))),
    );

    $form['extlink_mailto_class'] = array(
      '#type' => 'checkbox',
      '#title' => t('Place an icon next to mailto links'),
      '#return_value' => 'mailto',
      '#default_value' => $config->get('extlink_mailto_class'),
      '#description' => t('Places an !icon icon next to mailto links.', array('!icon' => _theme('image',array('uri' => drupal_get_path('module', 'extlink') . '/mailto.png', 'alt' => t('Email links icon'))))),
    );

    $form['extlink_img_class'] = array(
      '#type' => 'checkbox',
      '#title' => t('Place an icon next to image links'),
      '#return_value' => TRUE,
      '#default_value' => $config->get('extlink_img_class', FALSE),
      '#description' => t('If checked, images wrapped in an anchor tag will be treated as external links.'),
    );

    $form['extlink_subdomains'] = array(
      '#type' => 'checkbox',
      '#title' => t('Exclude links with the same primary domain.'),
      '#default_value' => $config->get('extlink_subdomains'),
      '#description' => t("For example, a link from 'www.example.com' to the subdomain of 'my.example.com' would be excluded."),
    );

    $form['extlink_target'] = array(
      '#type' => 'checkbox',
      '#title' => t('Open external links in a new window'),
      '#return_value' => '_blank',
      '#default_value' => $config->get('extlink_target'),
    );

    $form['extlink_alert'] = array(
      '#type' => 'checkbox',
      '#title' => t('Display a pop-up warning when any external link is clicked.'),
      '#return_value' => '_blank',
      '#default_value' => $config->get('extlink_alert'),
    );

    $form['extlink_alert_text'] = array(
      '#type' => 'textarea',
      '#title' => t('Text to display in the pop-up warning box.'),
      '#rows' => 3,
      '#default_value' => $config->get('extlink_alert_text'),
      '#description' => t('Text to display in the pop-up external link warning box.'),
      '#wysiwyg' => FALSE,
      '#states' => array(
      // Only show this field when user opts to display a pop-up warning.
      'visible' => array(
        ':input[name="extlink_alert"]' => array('checked' => TRUE),
      ),
    ),
   );

    $patterns = array(
      '<code>(example\.com)</code> ' . t('Matches example.com.'),
      '<code>(example\.com)|(example\.net)</code> ' . t('Multiple patterns can be strung together by using a pipe. Matches example.com OR example.net.'),
      '<code>(links/goto/[0-9]+/[0-9]+)</code> ' . t('Matches links that go through the <a href="http://drupal.org/project/links">Links module</a> redirect.'),
    );

    $wildcards = array(
      '<code>.</code> ' . t('Matches any character.'),
      '<code>?</code> ' . t('The previous character or set is optional.'),
      '<code>\d</code> ' . t('Matches any digit (0-9).'),
      '<code>[a-z]</code> ' . t('Brackets may be used to match a custom set of characters. This matches any alphabetic letter.'),
    );

    $form['patterns'] = array(
      '#type' => 'details',
      '#title' => t('Pattern matching'),
      '#description' =>
        '<p>' . t('External links uses patterns (regular expressions) to match the "href" property of links.') . '</p>' .
        t('Here are some common patterns.') .
        _theme('item_list', array('items' => $patterns)) .
        t('Common special characters:') .
        _theme('item_list', array('items' => $wildcards)) .
        '<p>' . t('All special characters (!character) must also be escaped with backslashes. Patterns are not case-sensitive. Any <a href="http://www.javascriptkit.com/javatutors/redev2.shtml">pattern supported by JavaScript</a> may be used.', array('!characters' => '<code>^ $ . ? ( ) | * +</code>')) . '</p>',
      '#open' => FALSE,
    );

    $form['patterns']['extlink_exclude'] = array(
      '#type' => 'textfield',
      '#title' => t('Exclude links matching the pattern'),
      '#maxlength' => NULL,
      '#default_value' => $config->get('extlink_exclude'),
      '#description' => t('Enter a regular expression for links that you wish to exclude from being considered external.'),
    );

    $form['patterns']['extlink_include'] = array(
      '#type' => 'textfield',
      '#title' => t('Include links matching the pattern'),
      '#maxlength' => NULL,
      '#default_value' => $config->get('extlink_include'),
      '#description' => t('Enter a regular expression for internal links that you wish to be considered external.'),
    );

    $form['css_matching'] = array(
      '#tree' => FALSE,
      '#type' => 'fieldset',
      '#title' => t('CSS Matching'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#description' =>
        '<p>' . t('Use CSS selectors to exclude entirely or only look inside explicitly specified classes and IDs for external links.  These will be passed straight to jQuery for matching.') . '</p>',
    );

    $form['css_matching']['extlink_css_exclude'] = array(
      '#type' => 'textarea',
      '#title' => t('Exclude links inside these CSS selectors'),
      '#maxlength' => NULL,
      '#default_value' => $config->get('extlink_css_exclude', ''),
      '#description' => t('Enter a comma-separated list of CSS selectors (ie "#block-block-2 .content, ul.menu")'),
    );

    $form['css_matching']['extlink_css_explicit'] = array(
      '#type' => 'textarea',
      '#title' => t('Only look for links inside these CSS selectors'),
      '#maxlength' => NULL,
      '#default_value' => $config->get('extlink_css_explicit', ''),
      '#description' => t('Enter a comma-separated list of CSS selectors (ie "#block-block-2 .content, ul.menu")'),
    );
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::config('extlink.settings')
      ->set('extlink_include', $form_state['values']['extlink_include'])
      ->set('extlink_exclude', $form_state['values']['extlink_exclude'])
      ->set('extlink_alert_text', $form_state['values']['extlink_alert_text'])
      ->set('extlink_alert', $form_state['values']['extlink_alert'])
      ->set('extlink_target', $form_state['values']['extlink_target'])
      ->set('extlink_subdomains', $form_state['values']['extlink_subdomains'])
      ->set('extlink_mailto_class', $form_state['values']['extlink_mailto_class'])
      ->set('extlink_img_class', $form_state['values']['extlink_img_class'])
      ->set('extlink_class', $form_state['values']['extlink_class'])
      ->set('extlink_css_exclude', $form_state['values']['extlink_css_exclude'])
      ->set('extlink_css_explicit', $form_state['values']['extlink_css_explicit'])
      ->save();

    parent::SubmitForm($form, $form_state);
  }

}
