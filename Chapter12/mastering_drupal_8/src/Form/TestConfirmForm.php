<?php
namespace Drupal\mastering_drupal_8\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Class TestConfirmForm
 */
class TestConfirmForm extends ConfirmFormBase {
  
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'test_confirm_form';
  }
  
  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to proceed?');
  }
  
  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('<front>');
  }
  
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirectUrl($this->getCancelUrl());
  }
}
