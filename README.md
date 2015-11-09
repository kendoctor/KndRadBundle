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
                class: %app.class.generator.some%
                arguments: []

If class suffix is same as root(relative bundle directory) directory, it will be trimmed.

Other possibilities:

        Acme\Bundle\SomeBundle\Builder\SubDir\SomeBuilder 
            => acme_some.class.generator.sub_dir_some #class container parameter
            => acme_some.generator.sub_dir_some #service id
        
**NOTE**
    
If you want a newly added class to be automatically injected, you need clear cache
    
        php app/console cache:clear
    

If the class constructor has parameters, for example:

    namespace AppBundle\Builder;
    use Knd\Bundle\RadBundle\DependencyInjection\AutoInjectInterface;
    
    class SomeBuilder implements AutoInjectInterface 
    {
        private $em;
        private $class;
        
        public function __construct($class, $em)
        {
            $this->class = $class;
            $this->em = $em;
        }
        
        public static function getConstructorParameters()
        {
            return array(
                '%app.class.entity.product%',
                '@doctrine.orm.entity_manager'
            );
        }
    }
    
As you can see:

  1. %app.class.entity.product% - will be a container parameter
  2. @service_id - will be a service
  

Other possibilities:

Form Type services, classes should implement ``Symfony\Component\Form\FormInterface``

    namespace AppBundle\Form\UserType
    class UserType extends AbstractType
        => app.class.form.user_type
        => app.form.type.user
    
Entity Repository services, class should extend ``Doctrine\ORM\EntityRepository``

    AppBundle\Repository\UserRepository 
        => app.class.repository.user #class container parameter
        => app.repository.user #service id

Default repository class directory is ``Repository``, 
If you want repository classes stay in the same dir with entity:
    
    //config.yml
    knd_rad:
        auto_inject:
            entity:
                repository:
                    dir: Entity

You also do not need specify entity class's repository in orm mapping:
    
    AppBundle\Entity\User
        type: entity
        repositoryClass: xxx # you can ignore this setting

If entity has no repository class, it will auto use ``Knd\Bundle\RadBundle\Repository\EntityRepository``

You can ignore auto feature by config:

    //config.yml
    knd_rad:
        auto_inject:
            entity:
                repository:
                    auto: false
                    
Manager services for entity classes, the manager classes should extend ``Knd\Bundle\RadBundle\Manager\Manager``

Default manager class directory is ``Manager``:

    AppBundle\Manager\UserManager
        => app.class.manager.user #class container parameter
        => app.manager.user #service id 

If entity has no manager class, it will auto use ``Knd\Bundle\RadBundle\Manager\Manager``.

You can ignore auto feature by config:

    //config.yml
    knd_rad:
        auto_inject:
            entity:
                manager:
                    auto: false
                    

Model class voter services, default directory is ``Security/Voter``,
Voter classes should extend ``Knd\Bundl\RadBundle\Security\Voter\AbstractVoter``

    AppBundle\Security\Voter\UserVoter
        => app.class.security.voter_user_voter
        => app.security.voter.user # the service is private, you can not access it from container


##Configuation##

    knd_rad:
        auto_inject:
            entity:
                dirs: [Entity]
                classes: []
                exclude_classes: []
                repository:
                    auto: true
                    dir: Repository
                manager:
                    auto: true
                    dir: Manager
                voter:
                    auto: true
                    dir: Security/Voter
            common:
                dirs: [Form]
                classes: []
                exclude_classes: []
                
                  
                  