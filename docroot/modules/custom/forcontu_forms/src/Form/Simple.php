<?php

namespace Drupal\forcontu_forms\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Session\AccountInterface;
use Egulias\EmailValidator\EmailValidator;

/**
 * Implements the Simple form controller.
 *
 * @see \Drupal\Core\Form\FormBase
 */

class Simple extends FormBase
{
  protected $database;
  protected $currentUser;
  protected $emailValidator;

  public function __construct(
    Connection $database,
    AccountInterface $current_user,
    EmailValidator $email_validator
  ) {
    $this->database = $database;
    $this->currentUser = $current_user;
    $this->emailValidator = $email_validator;
  }
  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('database'),
      $container->get('current_user'),
      $container->get('email.validator')
    );
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Título'),
      '#description' => $this->t('El título debe tener al menos 5 caracteres.'),
      '#required' => TRUE,
    ];

    $form['color'] = [
      '#type' => 'select',
      '#title' => $this->t('Color'),
      '#options' => [
        0 => $this->t('Black'),
        1 => $this->t('Red'),
        2 => $this->t('Blue'),
        3 => $this->t('Green'),
        4 => $this->t('Orange'),
        5 => $this->t('White'),
      ],
      '#default_value' => 2,
      '#description' => $this->t('Choose a color.'),
    ];

    $form['user_email'] = [
      '#type' => 'email',
      '#title' => $this->t('User email'),
      '#description' => $this->t('Your email.'),
      '#required' => TRUE,
      '#field_prefix' => '<div class="field_prefix">',
      '#field_suffix' => '</div>',
      '#prefix' => '<div class="prefix">',
      '#suffix' => '</div>',
      '#attributes' => ['class' => ['highlighted', 'forcontu']],
    ];

    $form['information'] = [
      '#type' => 'vertical_tabs',
    ];

    $form['personal_data'] = [
      '#type' => 'details',
      '#title' => $this->t('Personal Data'),
      '#description' => $this->t('Lorem ipsum dolor sit amet, consectetur adipiscing elit. '),
      '#group' => 'information',
    ];

    $form['personal_data']['first_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First name'),
      '#required' => TRUE,
      '#size' => 40,
    ];

    $form['personal_data']['last_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last name'),
      '#required' => TRUE,
      '#size' => 40,
    ];

    $form['access_data'] = [
      '#type' => 'details',
      '#title' => $this->t('Access Data'),
      '#description' => $this->t('Curabitur non semper diam. Mauris faucibus eu augue vel semper.'),
      '#group' => 'information',
    ];

    $form['access_data']['user_email'] = [
      '#type' => 'email',
      '#title' => $this->t('User email'),
      '#required' => TRUE,
    ];

    $form['access_data']['password'] = [
      '#type' => 'password_confirm',
      '#required' => TRUE,
    ];

    $form['username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Username'),
      '#description' => $this->t('Your username.'),
      '#default_value' => $this->currentUser->getAccountName(),
      '#required' => TRUE,
    ];

    $form['comment'] = [
      '#type' => 'item',
      '#markup' => $this->t('Item element <strong>to add HTML</strong> into a Form.'),
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  public function getFormId()
  {
    return 'forcontu_forms_simple';
  }

  public function validateForm(array &$form, FormStateInterface $form_state)
  {
    $title = $form_state->getValue('title');

    if (strlen($title) < 5) {
      // Set an error for the form element with a key of "title".
      $form_state->setErrorByName('title', $this->t('The title must be at least 5 characters long.'));
    }

    $email = $form_state->getValue('user_email');

    if (!$this->emailValidator->isValid($email)) {
      $form_state->setErrorByName('user_email', $this->t('%email is not a valid email address.', ['%email' => $email]));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $title = $form_state->getValue('title');
    $color = $form_state->getValue('color');
    $username = $form_state->getValue('username');
    $user_email = $form_state->getValue('user_email');

    $this->database->insert('forcontu_forms_simple')
      ->fields([
        'title' => $form_state->getValue('title'),
        'color' => $form_state->getValue('color'),
        'username' => $form_state->getValue('username'),
        'email' => $form_state->getValue('user_email'),
        'uid' => $this->currentUser->id(),
        'ip' => \Drupal::request()->getClientIP(),
        'timestamp' => REQUEST_TIME,
      ])
      ->execute();

    $this->messenger()->addMessage($this->t('The form has been submitted correctly'));
    $this->logger('forcontu_forms')->notice(
      'New Simple Form entry from user %username inserted: %title.',
      [
        '%username' => $form_state->getValue('username'),
        '%title' => $form_state->getValue('title'),
      ]
    );
    $form_state->setRedirect('forcontu_pages.simple');
  }
}

/**
 * Implements hook_schema().
 */
function forcontu_forms_schema()
{
  $schema['forcontu_forms_simple'] = [
    'description' => 'Stores Simple form data',
    'fields' => [
      'id' => [
        'type' => 'serial',
        'not null' => TRUE,
        'description' => "ID autoincrement",
      ],
      'title' => [
        'type' => 'varchar',
        'length' => 255,
        'not null' => TRUE,
        'default' => '',
        'description' => 'Title',
      ],
      'color' => [
        'type' => 'int',
        'not null' => TRUE,
        'default' => 0,
        'size' => 'tiny',
        'description' => 'Color code',
      ],
      'username' => [
        'type' => 'varchar',
        'length' => 255,
        'not null' => TRUE,
        'default' => '',
        'description' => 'Username',
      ],
      'email' => [
        'type' => 'varchar',
        'length' => 255,
        'not null' => TRUE,
        'default' => '',
        'description' => 'Email',
      ],
      'uid' => [
        'type' => 'int',
        'unsigned' => TRUE,
        'not null' => TRUE,
        'default' => 0,
        'description' => 'The uid of the user who submitted the form.',
      ],
      'ip' => [
        'type' => 'varchar',
        'length' => 128,
        'not null' => TRUE,
        'default' => '',
        'description' => 'User IP',
      ],
      'timestamp' => [
        'type' => 'int',
        'not null' => TRUE,
        'default' => 0,
        'description' => 'Unix timestamp indicating when the form was submitted.',
      ],
    ],
    'primary key' => ['id'],
  ];
  return $schema;
}
