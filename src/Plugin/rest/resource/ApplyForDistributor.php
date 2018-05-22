<?php

namespace Drupal\distribution\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\distribution\DistributionManagerInterface;
use Drupal\distribution\Entity\Distributor;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\user\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * 实现申请成为分销用户接口
 *
 * @RestResource(
 *   id = "distribution_apply_for_distributor",
 *   label = @Translation("Apply for distributor"),
 *   uri_paths = {
 *     "create" = "/api/rest/distribution/apply-for-distributor"
 *   }
 * )
 */
class ApplyForDistributor extends ResourceBase
{

    /**
     * A current user instance.
     *
     * @var \Drupal\Core\Session\AccountProxyInterface
     */
    protected $currentUser;

    /**
     * @var DistributionManagerInterface
     */
    protected $distributionManager;

    /**
     * Constructs a new ApplyForDistributor object.
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
        AccountProxyInterface $current_user, DistributionManagerInterface $distribution_manager)
    {
        parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

        $this->currentUser = $current_user;
        $this->distributionManager = $distribution_manager;
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
     * Responds to POST requests.
     *
     * @param  $data
     *
     * @return \Drupal\rest\ModifiedResourceResponse
     *   The HTTP response object.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     *   Throws exception expected.
     */
    public function post($data)
    {
        // You must to implement the logic of your REST Resource here.
        // Use current user after pass authentication to validate access.
        if (!$this->currentUser->hasPermission('access content')) {
            throw new AccessDeniedHttpException();
        }

        $upstream_distributor = null;
        if ($data['upstream_distributor_id']) $upstream_distributor = Distributor::load($data['upstream_distributor_id']);

        $distributor = $this->distributionManager
            ->createDistributor($this->currentUser->getAccount(), $upstream_distributor, 'approved', $data['agent']);

        return new ModifiedResourceResponse($distributor, 200);
    }

}
