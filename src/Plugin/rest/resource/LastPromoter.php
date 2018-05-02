<?php

namespace Drupal\distribution\Plugin\rest\resource;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\distribution\DistributionManager;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\user\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Translation\Exception\NotFoundResourceException;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "distribution_last_promoter",
 *   label = @Translation("Last promoter resource"),
 *   uri_paths = {
 *     "canonical" = "/api/rest/distribution/last-promoter"
 *   }
 * )
 */
class LastPromoter extends ResourceBase
{

    /**
     * A current user instance.
     *
     * @var \Drupal\Core\Session\AccountProxyInterface
     */
    protected $currentUser;

    /**
     * @var DistributionManager
     */
    protected $distributionManager;

    /**
     * Constructs a new LastPromoterResource object.
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
        AccountProxyInterface $current_user, DistributionManager $distributionManager)
    {
        parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

        $this->currentUser = $current_user;
        $this->distributionManager = $distributionManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
    {
        return new static(
            $configuration,
            $plugin_id,
            $plugin_definition,
            $container->getParameter('serializer.formats'),
            $container->get('logger.factory')->get('distribution'),
            $container->get('current_user'),
            $container->get('distribution.distribution_manager')
        );
    }

    /**
     * Responds to GET requests.
     *
     * @return \Drupal\rest\ResourceResponse
     *   The HTTP response object.
     *
     */
    public function get()
    {

        // You must to implement the logic of your REST Resource here.
        // Use current user after pass authentication to validate access.
        if (!$this->currentUser->hasPermission('access content')) {
            throw new AccessDeniedHttpException();
        }

        $promoter = $this->distributionManager->getLastPromoter($this->currentUser->getAccount());

        if ($promoter) {
            $response = new ResourceResponse($promoter->getDistributor(), 200);
            $response->addCacheableDependency(CacheableMetadata::createFromRenderArray([
                '#cache' => [
                    'max-age' => 0
                ],
            ]));
            return  $response;
        } else {
            throw new NotFoundHttpException('没有绑定推广者');
        }
    }
}
