#KndRadBundle#

Rapid Application Development bundle for Symfony2 inspired by KnpRadBundle

##Features###

 1. Mark class for auto dependency injection via tag interface.
 2. Simple Routing definition


##Installation##

    composer require knd/rad-bundle
    
Via composer install 

    class AppKernel extends Kernel
    {
        public function registerBundles()
        {
            $bundles = array(
                ...
                new \Knd\Bundle\RadBundle\KndRadBundle(),
                ...
            );
    
        }
        ...
    }
    
Config in ``AppKernel``

If you have a Bundle ``AppBundle``, follow as below

    namespace AppBundle;
    use Knd\Bundle\RadBundle\Bundle\Bundle;
    
    class AppBundle extends Bundle
    {
    }
    
##Auto Dependency Injection##

Tag class as DI service, it will be automatically injected into container.

###Auto Inject Class As Container Parameter###

    namespace AppBundle\Entity;
    use Knd\Bundle\RadBundle\TagInterface\AutoInjectClassParameterInterface;
    
    class User implements AutoInjectClassParameterInterface
    {
    }
    
This will inject name of class ``User`` as container parameter. Same as below:

    //.../config.yml
    
    parameters:
        app.class.entity.user
    
Other possibilities:

    AppBundle\Entity\Question\Selection => app.class.entity.question_selection
    
It can be used as arguments for services
    
    services:
        class: someClass
        arguments: [%app.class.entity.user%]
    
###Auto Inject Class As Service###

    
如果存在类``User``，实现了 ``AutoInjectManagerByFactoryInterface``

     namespace AppBundle\Entity;
     class User implements AutoInjectManagerByFactoryInterface
     {
     }
    
    services:
        knd.factory.manager:
            class: Knd\Bundle\RadBundle\Manager\ManagerFactory
            arguments:
                - @entity_manager
                - @knp_paginator    
                         
        app.manager.user
            class: Knd\Bundle\RadBundle\Manager\Manager
            factory: [@knd.factory.manager, create]
            arguments:
                - %app.class.user%
                
如果存在类``User``，实现了 ``AutoInjectClassParameterInterface``

     namespace AppBundle\Entity;
     class User implements AutoInjectClassParameterInterface
     {
     }
    
    $className = $container->getParameter('app.class.entity_user');
    
如果存在类``User``，实现了 ``AutoInjectServiceInterface``

    namespace AppBundle\Manager\UserManager;
    
    class UserManager implements AutoInjectServiceInterface
    {
    }
     
    app.manager.user:
        class: AppBundle\Manager\UserManager
            arguments: []
    
                    
This will produce a parameter in container ``app.class.entity_user`` referencing class
``AppBundle\Entity\User``

    $className = $container->getParameter('app.class.entity_user');
    //className == AppBundle\Entity\User
    
This also can be used as argument for service

    class: somethingClass
    arguments: [ %app.class.entity_user% ]
    
If you want to inject a class into container, implements KndRadServiceDiInterface

    namespace AppBundle\Manager;
    
    class UserManager implements KndRadServiceDiInterface
    {
        public function __construct()
        {
        }
    }
    
This will inject ``UserManager`` as service ``app.manager.user``

    class: AppBundle\Manager\UserManager
    arguments: []
    

If class constructor has parameters like this:

    /**
     *
     * @Knd\DiParams({normal = '2', diParameter='%app.class.name%', diService= '@serviceId' })
    **/
    public function __construct($normal, $diParameter, $diService)
    {
    }
    
    public function __construct($p_app__class__name_something, $s_app__manager__user)
    {
    }
    

    interface ManagerInterface
    {
        public function getClass();
        public function create();
        public function getRepository();
        public function getObjectManager();
        //public function createByType();
        public function getBuilder();
        public function getFormBuilder();
    }
    
    services:
        knd.factory.manager:
            class: Knd\Bundle\RadBundle\Manager\ManagerFactory
            
        app.manager.user
            class: Knd\Bundle\RadBundle\Manager\Manager
            factory: [@knd.factory.manager, create]
            arguments:
                - %app.class.user%
                - @entity_manager
                - @knp_paginator

   

    
        

##Configuration##

    knd_rad:
        auto_di:
            include_dir : [ Entity, Repository, Manager, Form ]
        name_convention: ~
        
            