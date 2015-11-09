#KndRadBundle#

Rapid Application Development bundle for Symfony2 inspired by KnpRadBundle

##Features###

 1. Auto Dependency Injection
 2. Entity Model Manager
 3. Model Security Voter 
  

##Installation##

    composer require knd/rad-bundle
    
After installation, add bundle into kernel. 

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
    
Extends you bundle with ``Knd\Bundle\RadBundle\Bundle\Bundle`` :

    namespace AppBundle;
    use Knd\Bundle\RadBundle\Bundle\Bundle;
    
    class AppBundle extends Bundle
    {
    }

    
##Auto Dependency Injection##

###Common class injected as service###

If you want to inject a normal class as a service, for example:

    namespace AppBundle\Generator;

    class SomeGenerator
    {
    }
    
    
Add ``KndRadBundle`` config in ``app/config.yml``
    
    knd_rad:
        auto_inject:
            common:
                #auto inject classes in the directories, default ``Form``
                dirs: [ Form, Generator, SomeDirectory/SubDirectory, ...]
                #auto inject specified classes, default None
                classes:
                    - AppBundle\SomeDirectory\SomeClass
                #exclude classes to prevent being injected if the classes exist in dirs, default None
                exclude_classes:
                    - AppBundle\OtherDirectory\OtherClass
    
For class ``AppBundle\Generator\SomeGenerator``
    
  1. Full name of the class injected as container parameter ``app.class.generator.some``
  2. The class inject into container as:
   
        services:
            app.generator.some
                class: %app.class.generator.some_generator%
                arguments: []

If class suffix is same as root(relative bundle directory) directory, it will be trimmed.

Other possibilities:

        Acme\Bundle\SomeBundle\Builder\SubDir\SomeBuilder 
            => acme_some.class.generator.sub_dir_some #class container parameter
            => acme_some.generator.sub_dir_some #service id
        
**NOTE**
    
If you want a newly added class to be automatically injected, you need clear cache
    
        php app/console cache:clear
    

 
   
    
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
            common:
                dirs: [Manager, Repository, Form ]
                exclude_dirs: []
                classes: []
                exclude_classes: []
                
        
        //get config of common
        //get classes of dirs without exclude dirs, merge and exclude specified classes,
        //if extends Knd\Bundle\RadBundle\Manager\Manager, auto inject as Entity Manager
        //if extends Symfony\Component\Form\FormInterface , auto inject as Form Type
        //if extends Doctrine\Common\ORM\EntityRepository, auto inject as Entity Repository

        
        //naming convention
        1. class container parameter
        AppBundle\Entity\User => app.class.entity.user
        AppBundle\Entity\Question\Selection => app.class.entity.question_selection
        KendoctorAppBundle\Entity\User => kendoctor_app.class.entity.user
        KendoctorAppBundle\Entity\Question\Selection => kendoctor_app.class.entity.question_selection
        
        2. entity repository services
        AppBundle\Entity\User => app.repository.user
        AppBundle\Entity\UserRepository => app.repository.user
        AppBundle\Repository\UserRepository => app.repository.user replace with before
        AppBundle\Repository\Question\SelectionRepository => app.repository.question_selection
                
        
        3. entity manager services
        AppBundle\Entity\User => app.manager.user
        AppBundle\Manager\UserManager => app.manager.user
        AppBundle\Other\UserManager => app.manager.other_user
        AppBundle\Manager\Other\UserManager => app.manager.other_user
        
        4. entity service info
        $ids = getServiceIdByEntity($class)
        
        
   
        //user roles: edit => pass, owner.edit
        class SomeController extends Controller {
        
            public function someAction()
            {
                $this->isGrantedOr403('app.entity.user.edit', $user');
            }
            
        }
        
        protected function isGranted($attribute, $object, $user = null)
        {
            //pass if super admin
            //edit => none => xxx.edit => xxx()
            
           
        
        }
        
        
        
                  
                  
                  
                  