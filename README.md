#KndRadBundle#

Rapid Application Development bundle for Symfony2 inspired by KnpRadBundle

##Features###

 1. Inject classes of specified directories as services.
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

###Auto Inject Entity Related Services###

Default Entity Directory is ``xxxBundle\Entity``, for example:

    namespace AppBundle\Entity;
    
    class User
    {
    }
    
The class name of ``User`` injected into container as parameter.

    app.class.entity.user
    
You can access its class name via container:

    $container->getParameter('app.class.entity.user') => AppBundle\Entity\User
    
    
By default, doctrine repository and manager service of ``User`` entity injected, just as below:

    services:
        app.manager.user:
            class: Knd\Bundle\RadBundle\Manager\Manager
            factory: [@knd_rad.factory.manager, create]
            arguments: [%app.class.entity.user%]    
                 
        app.repository.user:
            class: Doctrine\Common\Persistence\ObjectRepository
            factory: [@doctrine, getRepository]
            arguments: [%app.class.entity.user%]

Others possibilities:

    AppBundle\Entity\Question\Selection => app.class.entity.question_selection
    manager service => app.manager.question_selection
    repository service => app.repository.question_selection
    
###Auto Inject Form Type Services###

Default Form Type Directory is ``xxxBundle\Form``, for example:

    namespace AppBundle\Form;
    
    class UserType extends AbstractType
    {
    }
    
Classes should implements ``Symfony\Component\Form\FormTypeInterface``, just as below:

    services:
        app.form.type.user:
            class: %app.class.form.user_type%
            tags: { name: form.type, alias: app_user }
            
Others possibilities:
    
    AppBundle\Form\Question\SelectionType => app.class.form.question_selection_type
    form type service => app.form.type.question_selection
    
###Auto Inject Common Classes As Services###

Default common classes directories has ``xxxBundle\Manager``, for example:

    namespace AppBundle\Manager;

    class UserManager extend Manager
    {
    }
    
It will inject ``UserManager`` as service:
    
    services:
        app.manager.user:
            class: %app.class.manager.user%
            
If ``UserManager`` constructor has parameters:
        
        public function __construct(
                $p_app__class__entity__user,
                EntityManager $s_doctrine__orm__entity_manager
            )
        {
        }
        
It will inject as below:

    services:
        app.manager.user:
            class: %app.class.manager.user%
            arguments: 
                - %app.class.entity.user%
                - @doctrine.orm.entity_manager
        
You should follow naming convention:
 
    > ``$p_`` will be container parameter
     
    > ``$s_`` will be a service 
    
    > double ``_`` represents ``.``
    

> **NOTE**

Add new class into the specified directories, you should clear cache

    php app/console cache:clear
    
##Configuration##

    knd_rad:
        auto_inject:
            entity:
                dirs: [Entity] 
                manager: true #auto inject manager, you can bypass via creating service with same service id
                repository: true #auto inject doctrine repository
                ignore_suffix: [Repository] #exclude classes suffixed with Repository
            form_type:
                dirs: [Form]
            common:
                dirs: [Manager]
                classes: []
              
            