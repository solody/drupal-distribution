<?php

namespace Drupal\distribution\Plugin\rest\resource;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\distribution\Entity\Distributor;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Provides a resource to get view modes by entity and bundle.
 *
 * @RestResource(
 *   id = "distribution_update_distributor_setting",
 *   label = @Translation("Update distributor setting"),
 *   uri_paths = {
 *     "create" = "/api/rest/distribution/update-distributor-setting/{distributor}"
 *   }
 * )
 */
class UpdateDistributorSetting extends ResourceBase
{

    /**
     * A current user instance.
     *
     * @var \Drupal\Core\Session\AccountProxyInterface
     */
    protected $currentUser;

    /**
     * Constructs a new UpdateDistributorSetting object.
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
        AccountProxyInterface $current_user)
    {
        parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

        $this->currentUser = $current_user;
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
            $container->get('current_user')
        );
    }

    /**
     * Responds to POST requests.
     *
     * @param Distributor $distributor
     * @param $data
     * @return \Drupal\rest\ModifiedResourceResponse
     *   The HTTP response object.
     *
     */
    public function post(Distributor $distributor, $data)
    {

        // You must to implement the logic of your REST Resource here.
        // Use current user after pass authentication to validate access.
        if (!$this->currentUser->hasPermission('access content')) {
            throw new AccessDeniedHttpException();
        }

        if (isset($data['name'])) {
            $distributor->setName($data['name']);
        }

        if (isset($data['enable_distributor_brand'])) {
            if ((boolean)$data['enable_distributor_brand']) {
                $distributor->enableDistributorBrand();
            } else {
                $distributor->disableDistributorBrand();
            }
        }

        $distributor->save();

        return new ModifiedResourceResponse($distributor, 200);
    }

    /**
     * {@inheritdoc}
     */
    protected function getBaseRoute($canonical_path, $method)
    {
        $route = parent::getBaseRoute($canonical_path, $method);
        $parameters = $route->getOption('parameters') ?: [];
        $parameters['distributor']['type'] = 'entity:distribution_distributor';
        $route->setOption('parameters', $parameters);

        return $route;
    }
}
