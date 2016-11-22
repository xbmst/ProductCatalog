<?php

namespace AppBundle\Menu;


use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Builder implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function mainMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');

        $menu->addChild('Home', array('route' => 'homepage'));

        $em = $this->container->get('doctrine')->getManager();

        $product = $em->getRepository('AppBundle:Product')->findAll();

        $menu->addChild('Products', [
            'route' => 'admin_product_list',
            //'routeParameters' =>
        ]);
        $menu['Products']->addChild('Add product', [
                'route' => 'admin_product_new',
            ]
        );

        $menu->addChild('Users', [
           'route' => 'admin_user_list',
        ]);

        return $menu;
    }
}