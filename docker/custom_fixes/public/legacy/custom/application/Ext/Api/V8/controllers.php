<?php
use Api\V8\BeanDecorator\BeanManager;
use Slim\Container;

require_once('custom/application/Ext/Api/V8/Controller/PartyController.php');
return [PartyController::class => function(Container $container) {
    $beanManager = $container->get(BeanManager::class);
    return new PartyController($beanManager);
}];