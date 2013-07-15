<?php

/**
 * @file
 * Contains \Drupal\extlink\Form\ExtlinkAdminSettingsForm.
 */

namespace Drupal\extlink\Form;

use Drupal\Core\ControllerInterface;
use Drupal\system\SystemConfigFormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Displays the extlink settings form.
 */
class ExtlinkAdminSettingsForm extends SystemConfigFormBase {

  /**
   * Implements \Drupal\Core\Form\FormInterface::getFormID().
   */
  public function getFormID() {
    return 'extlink_admin_settings';
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface::buildForm().
   */
  public function buildForm(array $form, array &$form_state) {
    $config = $this->configFactory->get('extlink.settings');

    $form['extlink_class'] = array(
      '#type' => 'checkbox',
      '#title' => t('Place an icon next to external links.'),
      '#return_value' => 'ext',
      '#default_value' => $config->get('extlink_class'),
      '#description' => t('Places an !icon icon next to external links.', array('!icon' => theme('image', array('uri' => drupal_get_path('module', 'extlink') . '/extlink.png', 'alt' => t('External Links icon'))))),
    );

    $form['extlink_mailto_class'] = array(
      '#type' => 'checkbox',
      '#title' => t('Place an icon next to mailto links'),
      '#return_value' => 'mailto',
      '#default_value' => $config->get('extlink_mailto_class'),
      '#description' => t('Places an !icon icon next to mailto links.', array('!icon' => theme('image',array('uri' => drupal_get_path('module', 'extlink') . '/mailto.png', 'alt' => t('Email links icon'))))),
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
      '<em>(example\.com)</em> ' . t('Matches example.com.'),
      '<em>(example\.com)|(example\.net)</em> ' . t('Multiple patterns can be strung together by using a pipe. Matches example.com OR example.net.'),
      '<em>(links/goto/[0-9]+/[0-9]+)</em> ' . t('Matches links that go through the <a href="http://drupal.org/project/links">Links module</a> redirect.'),
    );

    $wildcards = array(
      '<em>.</em> ' . t('Matches any character.'),
      '<em>?</em> ' . t('The previous character or set is optional.'),
      '<em>\d</em> ' . t('Matches any digit (0-9).'),
      '<em>[a-z]</em> ' . t('Brackets may be used to match a custom set of characters. This matches any alphabetic letter.'),
    );

    $form['patterns'] = array(
      '#type' => 'details',
      '#title' => t('Pattern matching'),
      '#description' =>
        '<p>' . t('External links uses patterns (regular expressions) to match the "href" property of links.') . '</p>' .
        t('Here are some common patterns.') .
        theme('item_list', array('items' => $patterns)) .
        t('Common special characters:') .
        theme('item_list', array('items' => $wildcards)) .
        '<p>' . t('All special characters (<em>^ $ . ? ( ) | * +</em>) must also be escaped with backslashes. Patterns are not case-sensitive. Any <a href="http://www.javascriptkit.com/javatutors/redev2.shtml">pattern supported by JavaScript</a> may be used.') . '</p>',
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
  
    return parent::buildForm($form, $form_state);
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface:validateForm()
   */
  public function validateForm(array &$form, array &$form_state) {
   
    parent::validateForm($form, $form_state);
  }

  /**
   * Implements \Drupal\Core\Form\FormInterface:submitForm()
   *
   * @see book_remove_button_submit()
   */
  public function submitForm(array &$form, array &$form_state) {
    $this->configFactory->get('extlink.settings')
      ->set('extlink_include', $form_state['values']['extlink_include'])
      ->set('extlink_exclude', $form_state['values']['extlink_exclude'])
      ->set('extlink_alert_text', $form_state['values']['extlink_alert_text'])
      ->set('extlink_alert', $form_state['values']['extlink_alert'])
      ->set('extlink_target', $form_state['values']['extlink_target'])
      ->set('extlink_subdomains', $form_state['values']['extlink_subdomains'])
      ->set('extlink_mailto_class', $form_state['values']['extlink_mailto_class'])
      ->set('extlink_class', $form_state['values']['extlink_class'])
      ->save();

    parent::SubmitForm($form, $form_state);
  }

}