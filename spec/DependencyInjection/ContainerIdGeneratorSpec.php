<?php

namespace spec\Knd\Bundle\RadBundle\DependencyInjection;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ContainerIdGeneratorSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Knd\Bundle\RadBundle\DependencyInjection\ContainerIdGenerator');
    }

    function it_gets_bundle_class_which_the_class_exists()
    {
        $class = 'Knd\Bundle\RadBundle\Entity\User';
        $this->getBundleClass($class)->shouldBe('Knd\Bundle\RadBundle');

        $class = 'AppBundle\Entity\User';
        $this->getBundleClass($class)->shouldBe('AppBundle');

    }

    function it_gets_repository_service_id_by_entity_class()
    {

        $class = 'Knd\Bundle\AppBundle\Model\Question\Selection';
        $this->getEntityRepositoryServiceId($class)->shouldBe('knd_app.repository.question_selection');

        $class = 'AppBundle\Model\Question\Selection';
        $this->getEntityRepositoryServiceId($class)->shouldBe('app.repository.question_selection');

        $class = 'AppBundle\Entity\User';
        $this->getEntityRepositoryServiceId($class)->shouldBe('app.repository.user');

        $class = 'AppBundle\Entity\Question\Selection';
        $this->getEntityRepositoryServiceId($class)->shouldBe('app.repository.question_selection');

    }

    function it_gets_container_parameter_of_class()
    {
        $class = 'Knd\Bundle\AppBundle\Model\Question\Selection';
        $this->getContainerParameter($class)->shouldBe('knd_app.class.model.question_selection');

        $class = 'AppBundle\Model\Question\Selection';
        $this->getContainerParameter($class)->shouldBe('app.class.model.question_selection');

        $class = 'AppBundle\Entity\User';
        $this->getContainerParameter($class)->shouldBe('app.class.entity.user');

        $class = 'AppBundle\Entity\Question\Selection';
        $this->getContainerParameter($class)->shouldBe('app.class.entity.question_selection');
    }

    function it_guesses_repository_class_by_entity_class()
    {

        $class = 'AppBundle\Entity\User';
        $this->guessEntityRepositoryClass($class)->shouldBe('AppBundle\Entity\UserRepository');

        $this->guessEntityRepositoryClass($class, 'Repository')->shouldBe('AppBundle\Repository\UserRepository');

        $class = 'AppBundle\Entity\Question\Selection';
        $this->guessEntityRepositoryClass($class)->shouldBe('AppBundle\Entity\Question\SelectionRepository');

        $this->guessEntityRepositoryClass($class, 'Repository')->shouldBe('AppBundle\Repository\Question\SelectionRepository');

    }

    function it_guesses_manager_class_by_entity_class()
    {
        $class = 'AppBundle\Entity\User';
        $this->guessManagerClass($class)->shouldBe('AppBundle\Manager\UserManager');

        $this->guessManagerClass($class, 'Other')->shouldBe('AppBundle\Other\UserManager');

        $class = 'AppBundle\Entity\Question\Selection';
        $this->guessManagerClass($class)->shouldBe('AppBundle\Manager\Question\SelectionManager');

        $this->guessManagerClass($class, 'Other')->shouldBe('AppBundle\Other\Question\SelectionManager');
    }

    function it_gets_form_type_alias()
    {
        $class = 'Knd\Bundle\AppBundle\Form\Type\Sub\ProfileUserType';
        $this->getFormTypeAlias($class)->shouldBe('knd_app_sub_profile_user');

        $class = 'Knd\Bundle\AppBundle\Form\Type\Sub\UserType';
        $this->getFormTypeAlias($class)->shouldBe('knd_app_sub_user');

        $class = 'AppBundle\Form\Type\Sub\UserType';
        $this->getFormTypeAlias($class)->shouldBe('app_sub_user');

        $class = 'AppBundle\Form\UserType';
        $this->getFormTypeAlias($class)->shouldBe('app_user');


        $class = 'AppBundle\Form\Type\UserType';
        $this->getFormTypeAlias($class)->shouldBe('app_user');


    }

    function its_gets_action_role_prefix_of_class()
    {
        $class = 'AppBundle\Entity\User';
        $this->getActionRolePrefix($class)->shouldBe('app.role.entity_user');

        $class = 'AppBundle\Entity\AdminUser';
        $this->getActionRolePrefix($class)->shouldBe('app.role.entity_admin_user');

        $class = 'AppBundle\Entity\Sub\User';
        $this->getActionRolePrefix($class)->shouldBe('app.role.entity_sub_user');
    }
}
