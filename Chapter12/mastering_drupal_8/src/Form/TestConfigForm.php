<?php
namespace Drupal\mastering_drupal_8\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class TestConfigForm
 */
class TestConfigForm extends ConfigFormBase {
  
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'test_config_form';
  }
  
  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['mastering_drupal_8.settings'];
  }
  
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('mastering_drupal_8.settings');
    
    $form['vals'] = array(
      '#type' => 'details',
      '#title' => $this->t('Rows'),
      '#open' => TRUE,
    );
    
    // Wrapper around rows
    $form['vals']['rows'] = array(
      '#type' => 'item',
      '#tree' => TRUE,
      '#prefix' => '<div id="rows__replace">',
      '#suffix' => '</div>',
    );
    
    $count = $form_state->getValue('count', 1);
    
    for ($i = 0; $i < $count; $i++) {
      // Make sure we don't overwrite existing rows
      if (!isset($form['vals']['rows'][$i])) {
        $form['vals']['rows'][$i] = array(
          '#type' => 'url',
          '#title' => $this->t('URL %num', [ '%num' => $i ]),
        );
      }
    }
    
    $form['count'] = array(
      '#type' => 'value',
      '#value' => $count,
    );
    
    $form['add'] = array(
      '#type' => 'submit',
      '#name' => 'add',
      '#value' => $this->t('Add row'),
      '#submit' => [ [ $this, 'addRow' ] ],
      '#ajax' => [
        'callback' => [ $this, 'ajaxCallback' ],
        'wrapper' => 'rows__replace',
        'effect' => 'fade'
      ]
    );
    
    return parent::buildForm($form, $form_state);
  }
  
  /**
   * Increments the row count
   */
  public function addRow(array &$form, FormStateInterface &$form_state) {
    $count = $form_state->getValue('count', 1);
    $count += 1;
    $form_state->setValue('count', $count);
    $form_state->setRebuild(TRUE);
  }
  
  /**
   * Returns the array of row elements
   */
  public function ajaxCallback(array &$form, FormStateInterface &$form_state) {
    return $form['vals']['rows'];
  }
  
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('mastering_drupal_8.settings')
    ->set('name', $form_state->getValue('name'))
    ->save();
    
    parent::submitForm($form, $form_state);
  }
}
