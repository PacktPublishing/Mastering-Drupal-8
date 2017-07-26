<?php

namespace Drupal\mastering_drupal_8\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Psr\Log\LoggerInterface;
use Drupal\Core\Url;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "user_posts_resource",
 *   label = @Translation("User posts resource"),
 *   uri_paths = {
 *     "canonical" = "/user/{user}/posts"
 *   }
 * )
 */
class UserPostsResource extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a Drupal\rest\Plugin\ResourceBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   A current user instance.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    array $serializer_formats,
    LoggerInterface $logger,
    AccountProxyInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('mastering_drupal_8'),
      $container->get('current_user')
    );
  }
  
  /**
   * Returns a link to the Resource for a given user and page
   *
   * @param number $user
   * @param number $page
   *
   * @return string
   */
  protected function getPagerLink($user = 0, $page = 0) {
    return URL::fromRoute('rest.user_posts_resource.GET.hal_json',
        [ 'user' => $user, 'page' => $page ], [ 'absolute' => TRUE])
        ->setRouteParameter('_format', 'hal_json')
        ->toString(TRUE)
        ->getGeneratedUrl();
  }
  
  /**
   * Returns whether the provided user should be able to see un-published content
   *
   * @param \Drupal\user\Entity\User $user
   *
   * @return Boolean
   */
  protected function canSeeUnpublished($user) {
    $filter_unpublished = TRUE;
    // If user can bypass node access don't filter by published
    if ($this->currentUser->hasPermission('bypass node access')) {
      $filter_unpublished = FALSE;
    }
    // If there are node access
    else if (count(\Drupal::moduleHandler()->getImplementations('node_grants'))
        && node_access_view_all_nodes($this->currentUser)) {
      $filter_unpublished = FALSE;
    }
    // If current user and can view own unpublished content
    else if ($user->id() == $this->currentUser->id() 
        && $this->currentUser->hasPermission('view own unpublished content')) {
      $filter_unpublished = FALSE;
    }
    
    return !$filter_unpublished;
  }
  

  /**
   * Responds to GET requests.
   *
   * Returns a list of bundles for specified entity.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function get($user) {
    $page = \Drupal::request()->query->has('page') ? \Drupal::request()->query->get('page') : 0;
    
    // Load the user being requested
    $request_user = User::load($user);
    
    // Ensure the requesting user is able to view the requested user
    $entity_access = $request_user->access('view', $this->currentUser, TRUE);
    
    if (!$entity_access->isAllowed()) {
      throw new AccessDeniedHttpException();
    }
    
    $filter_unpublished = !$this->canSeeUnpublished($request_user);
    
    // Get count of all nodes authored by the user
    $count_query= \Drupal::entityQuery('node')
      ->condition('uid', $user);
    
    if ($filter_unpublished) {
      $count_query->condition('status', 1);
    }
    
    $count_query->count()
      ->addTag('node_access')
      ->addMetaData('op', 'view')
      ->addMetaData('account', $this->currentUser->getAccount());
    
      
    $pages = floor($count / 10);
    
    // Load all nodes authored by the user
    $node_query = \Drupal::entityQuery('node')
      ->condition('uid', $user);
    
    if ($filter_unpublished) {
      $node_query->condition('status', 1);
    }
    
    $node_query->sort('created', 'ASC')
      ->pager()
      ->addTag('node_access')
      ->addMetaData('op', 'view')
      ->addMetaData('account', $this->currentUser->getAccount());
    
    $nids = $node_query->execute();
    
    // Load the nodes
    $nodes = (sizeof($nids)) ? Node::loadMultiple($nids) : [];
    
    $links = [
      'self' => [ 'href' => $this->getPagerLink($user, $page) ],
      'start' => [ 'href' => $this->getPagerLink($user, 0) ],
      'last' => [ 'href' => $this->getPagerLink($user, $pages) ],
    ];
    
    if (0 < $page) {
      $links['prev'] = [ 'href' => $this->getPagerLink($user, ($page - 1)) ];
    }
    if (0 < $pages && $page != $pages) {
      $links['next'] = [ 'href' => $this->getPagerLink($user, ($page + 1)) ];
    }
    
    $response = new ResourceResponse([
      '_links' => $links,
      'total' => $count,
      'posts' => array_values($nodes)
    ]);
    
    // Add URL cache context
    $metadata = new CacheableMetadata();
    $metadata->addCacheContexts([ 'url.query_args:page' ]);
    $response->addCacheableDependency($metadata);
    $response->addCacheableDependency($entity_access);
    
    return $response;
  }

}
