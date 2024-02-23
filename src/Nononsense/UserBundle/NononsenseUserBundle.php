<?php

namespace Nononsense\UserBundle;

use Nononsense\UserBundle\DependencyInjection\Security\Factory\FormLdapFactory;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class NononsenseUserBundle extends Bundle
{
	public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new FormLdapFactory());
    }
}
