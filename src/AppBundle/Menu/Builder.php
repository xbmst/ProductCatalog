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
        $menu = $factory->createItem('Home');

        $menu->addChild('Home', ['route' => 'homepage']);

        $em = $this->container->get('doctrine')->getManager();
        $category = $em->getRepository('AppBundle:ProductCategory')->findAll();

        $menu->addChild('Products', [
            'route' => 'admin_product_list',
            ]);

        $menu['Products']->addChild('Add product', [
            'route' => 'admin_product_new',
            ]);

        $menu->addChild('Users', [
           'route' => 'admin_user_list',
        ]);

        //dump(count($category));
        $menu->addChild('Categories', [
            'route' => 'admin_category_list'
        ]);

        return $menu;
    }
}