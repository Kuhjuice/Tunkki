<?php

declare(strict_types=1);

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;

/**
 * @extends AbstractAdmin<object>
 */
final class ProductAdmin extends AbstractAdmin
{
    protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
    {
        return 'product';
    }
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('stripeData')
            ->add('event');
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('name')
            ->add('active')
            ->add('event')
            ->add('amount', MoneyType::class, [
                'currency' => 'EUR',
                'divisor' => 100
            ])
            ->add('customAmount')
            ->add('quantity')
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ],
            ]);
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('quantity')
            ->add('event');
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('stripeId')
            ->add('stripePriceId')
            ->add('stripeData');
    }
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('show');
        $collection->add('fetch_from_stripe', 'fetch-from-stripe');
    }
    public function configureTabMenu(\Knp\Menu\ItemInterface $menu, $action, \Sonata\AdminBundle\Admin\AdminInterface $childAdmin = null): void
    {
        $menu->addChild('fetchFromStripe', [
            'route' => 'admin_app_product_fetch_from_stripe',
        ])->setAttribute('icon', 'fa fa-balance-scale');
    }
}
