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
        app.class.entity.user: AppBundle\Entity\User
    
Other possibilities:

    AppBundle\Entity\Question\Selection => app.class.entity.question_selection
    
It can be used as arguments for services
    
    services:
        some_service_id:
            class: someClass
            arguments: [%app.class.entity.user%]
    
###Auto Inject Class As Service###

    namespace AppBundle\Builder;
    use Knd\Bundle\RadBundle\TagInterface\AutoInjectServiceInterface;
    
    class ProductBuilder implements AutoInjectServiceInterface
    {
    }
    
This will inject class ``ProductBuilder`` as a service, same as:

    services:
        app.builder.product:
            class: AppBundle\Builder\ProductBuilder
            arguments: []

If class constructor has parameters, for example:
    
    public function __construct(
        $p_app__class__entity__user,
        EntityManager $s_doctrine__orm__entity_manager
    )
    {
    }
    
This will be:

    services:
        app.builder.product:
            class: AppBundle\Builder\ProductBuilder
            arguments:
                - %app.class.entity.user%
                - @doctrine.orm.entity_manager

You should follow naming convention: 

> ``$p_`` will be container parameter
 
> ``$s_`` will be a service 

> double ``_`` represents ``.``


###Auto Inject Repository of Entity Class###

If you have an entity class:

    namespace AppBundle\Entity;
    use Knd\Bundle\RadBundle\TagInterface\AutoInjectDoctrineRepositoryInterface;
    
    class User implements AutoInjectDoctrineRepositoryInterface
    {
    }
    
This will be same as:

    services:
        app.repository.user:
            factory: [@doctrine, getRepository]
            arguments: [%app.class.entity.user%]


###Auto Inject Manager of Class###

If you have an entity class:

    namespace AppBundle\Entity;
    use Knd\Bundle\RadBundle\TagInterface\AutoInjectManagerByFactoryInterface;
    
    class User implements AutoInjectManagerByFactoryInterface
    {
    }

This will be same as:

    services:
        app.manager.user:
            factory: [@knd_rad.factory.manager, create]
            arguments: [%app.class.entity.user%]
    



##Configuration##

    knd_rad:
        auto_di:
            include_dir : [ Entity, Repository, Manager, Form ]
        name_convention: ~
        
            