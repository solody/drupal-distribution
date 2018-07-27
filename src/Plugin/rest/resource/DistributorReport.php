<?php

namespace Drupal\distribution\Plugin\rest\resource;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\distribution\DistributionManager;
use Drupal\distribution\Entity\Distributor;
use Drupal\finance\FinanceManagerInterface;
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
 *   id = "distribution_distributor_report",
 *   label = @Translation("Distributor report"),
 *   uri_paths = {
 *     "canonical" = "/api/rest/distribution/distributor-report/{distributor}"
 *   }
 * )
 */
class DistributorReport extends ResourceBase
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
     * Drupal\finance\FinanceManagerInterface definition.
     *
     * @var \Drupal\finance\FinanceManagerInterface
     */
    protected $financeManager;

    /**
     * Constructs a new DistributorReport object.
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
        AccountProxyInterface $current_user,
        DistributionManager $distributionManager,
        FinanceManagerInterface $financeManager)
    {
        parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);

        $this->currentUser = $current_user;
        $this->distributionManager = $distributionManager;
        $this->financeManager = $financeManager;
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
            $container->get('distribution.distribution_manager'),
            $container->get('finance.finance_manager')
        );
    }

    /**
     * Responds to GET requests.
     *
     * @param Distributor $distributor
     * @return \Drupal\rest\ResourceResponse
     *   The HTTP response object.
     *
     * @throws \Exception
     */
    public function get(Distributor $distributor)
    {
        // You must to implement the logic of your REST Resource here.
        // Use current user after pass authentication to validate access.
        if (!$this->currentUser->hasPermission('access content')) {
            throw new AccessDeniedHttpException();
        }

        $pending_account = $this->financeManager->getAccount($distributor->getOwner(), DistributionManager::FINANCE_PENDING_ACCOUNT_TYPE);
        $main_account = $this->financeManager->getAccount($distributor->getOwner(), DistributionManager::FINANCE_ACCOUNT_TYPE);

        $data = [
            'normal' => [
                'global' => [
                    'total_promoted' => $this->distributionManager->countPromoters($distributor),
                    'total_orders' => $this->distributionManager->countOrders($distributor),
                    'total_commission' => $this->distributionManager->countCommissionTotalAmount($distributor)->toArray(),
                    'total_commission_chain' => $this->distributionManager->countCommissionTotalAmount($distributor, 'china')->toArray(),
                    'total_commission_promotion' => $this->distributionManager->countCommissionTotalAmount($distributor, 'promotion')->toArray(),
                    'total_commission_leader' => $this->distributionManager->countCommissionTotalAmount($distributor, 'leader')->toArray()
                ],
                'recent' => [
                    'month' => [
                        'total_promoted' => $this->distributionManager->countPromoters($distributor, 30),
                        'total_orders' => $this->distributionManager->countOrders($distributor, 30),
                        'total_commission' => $this->distributionManager->countCommissionTotalAmount($distributor, null, 30)->toArray(),
                        'total_commission_chain' => $this->distributionManager->countCommissionTotalAmount($distributor, 'china', 30)->toArray(),
                        'total_commission_promotion' => $this->distributionManager->countCommissionTotalAmount($distributor, 'promotion', 30)->toArray(),
                        'total_commission_leader' => $this->distributionManager->countCommissionTotalAmount($distributor, 'leader', 30)->toArray()
                    ]
                ]
            ],
            'finance' => [
                'balance' => [
                    'main' => $main_account->getBalance()->toArray(),
                    'pending' => $pending_account->getBalance()->toArray(),
                ],
                'withdraw' => [
                    'transferred' => $this->financeManager->countCompleteWithdrawTotalAmount($main_account)->toArray(),
                    'pending' => $this->financeManager->countPendingWithdrawTotalAmount($main_account)->toArray()
                ]
            ]
        ];

        $response = new ResourceResponse($data, 200);
        $response->addCacheableDependency($distributor);
        $response->addCacheableDependency(CacheableMetadata::createFromRenderArray([
            '#cache' => [
                'max-age' => 0
            ]
        ]));

        return $response;
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
