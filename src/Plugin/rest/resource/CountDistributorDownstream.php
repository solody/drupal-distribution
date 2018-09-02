<?php

namespace Drupal\distribution\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\distribution\Entity\Distributor;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "distribution_count_distributor_downstream",
 *   label = @Translation("Count distributor downstream"),
 *   uri_paths = {
 *     "create" = "/api/rest/distribution/count-distributor-downstream"
 *   }
 * )
 */
class CountDistributorDownstream extends ResourceBase {

  /**
   * A current user instance.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs a new CountDistributorDownstream object.
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
      $container->get('logger.factory')->get('distribution'),
      $container->get('current_user')
    );
  }

  /**
   * Responds to POST requests.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity object.
   *
   * @return \Drupal\rest\ModifiedResourceResponse
   *   The HTTP response object.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
  public function post($data) {

    // You must to implement the logic of your REST Resource here.
    // Use current user after pass authentication to validate access.
    if (!$this->currentUser->hasPermission('access content')) {
      throw new AccessDeniedHttpException();
    }

    $distributors = Distributor::loadMultiple();

    $getDownstream = function ($upstream_distributor = null) use ($distributors) {
      $rs = [];
      foreach ($distributors as $distributor) {
        /** @var Distributor $distributor */
        if ($upstream_distributor instanceof Distributor) {
          if ($distributor->getUpstreamDistributor() instanceof Distributor && $distributor->getUpstreamDistributor()->id() === $upstream_distributor->id()) {
            $rs[] = $distributor;
          }
        } else {
          if (empty($distributor->getUpstreamDistributor())) {
            $rs[] = $distributor;
          }
        }
      }
      return $rs;
    };

    $current_level = 1;
    $current_upstream_distributor = isset($data['distributor']) ? Distributor::load($data['distributor']) : null;
    $current_distributors = $getDownstream($current_upstream_distributor);

    $level_start = isset($data['level_start']) ? (int)$data['level_start'] : null;
    $level_end = isset($data['level_end']) ? (int)$data['level_end'] : null;

    $count = 0;
    do {
      if (($level_start && $level_end && $level_start <= $current_level && $current_level <= $level_end) ||
        ($level_start && !$level_end && $level_start <= $current_level) ||
        (!$level_start && $level_end && $current_level <= $level_end) ||
        (!$level_start && !$level_end)) {
        $count += count($current_distributors);
      }

      $current_level++;
      $new_current_distributors = [];
      foreach ($current_distributors as $current_distributor) {
        $new_current_distributors += $getDownstream($current_distributor);
      }
      $current_distributors = $new_current_distributors;
    } while(count($current_distributors));


    return new ModifiedResourceResponse($count, 200);
  }

  /**
   * 不检查权限
   * @inheritdoc
   */
  public function permissions() {
    return [];
  }
}
