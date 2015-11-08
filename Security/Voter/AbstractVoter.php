<?php
/**
 * Created by PhpStorm.
 * User: kendoctor
 * Date: 15/11/7
 * Time: 上午11:14
 */

namespace Knd\Bundle\RadBundle\Security\Voter;

use Knd\Bundle\RadBundle\DependencyInjection\ContainerIdGenerator;
use Knd\Bundle\RadBundle\Security\OwnerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AbstractVoter as BaseAbstractVoter;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractVoter extends BaseAbstractVoter implements VoterInterface
{
    protected $containerIdGenerator;
    protected $class;

    public function __construct($class)
    {
        $this->containerIdGenerator = new ContainerIdGenerator();
        $this->class = $class;
    }

    protected function getRolePrefix()
    {
        return $this->containerIdGenerator->getActionRolePrefix($this->class);
    }

    protected function newRole($action, $callbackVote = null )
    {
        if($callbackVote)
        {

            return sprintf('%s.%s.%s', $this->getRolePrefix(), $callbackVote, $action);
        }

        return sprintf('%s.%s', $this->getRolePrefix(), $action);
    }

    public abstract function getSupportedRoles();


    /**
     * Return an array of supported classes. This will be called by supportsClass.
     *
     * @return array an array of supported classes, i.e. array('Acme\DemoBundle\Model\Product')
     */
    protected function getSupportedClasses()
    {
        return array($this->class);
    }

    /**
     * Return an array of supported attributes. This will be called by supportsAttribute.
     *
     * @return array an array of supported attributes, i.e. array('CREATE', 'READ')
     */
    protected function getSupportedAttributes()
    {
        return $this->getSupportedRoles();
    }


    /**
     * @param TokenInterface $token
     * @param object $object
     * @param array $attributes
     * @return int
     */
    public function vote(TokenInterface $token, $object, array $attributes)
    {
        if (is_string($object) && class_exists($object) && !$this->supportsClass($object)) {
            return self::ACCESS_ABSTAIN;

        }elseif (!is_object($object) || !$object || !$this->supportsClass(get_class($object))) {

            return self::ACCESS_ABSTAIN;
        }

        // abstain vote by default in case none of the attributes are supported
        $vote = self::ACCESS_ABSTAIN;

        foreach ($attributes as $attribute) {
            if (!$this->supportsAttribute($attribute)) {
                continue;
            }

            // as soon as at least one attribute is supported, default is to deny access
            $vote = self::ACCESS_DENIED;

            if ($this->isGranted($attribute, $object, $token->getUser())) {
                // grant access as soon as at least one voter returns a positive response
                return self::ACCESS_GRANTED;
            }
        }

        return $vote;
    }


    public function getSuperAdminRole()
    {
        return 'ROLE_SUPER_ADMIN';
    }


    /**
     * Perform a single access check operation on a given attribute, object and (optionally) user
     * It is safe to assume that $attribute and $object's class pass supportsAttribute/supportsClass
     * $user can be one of the following:
     *   a UserInterface object (fully authenticated user)
     *   a string               (anonymously authenticated user).
     *
     * @param string $attribute
     * @param object $object
     * @param UserInterface|string $user
     * @return bool
     * @throws \Exception
     */
    protected function isGranted($attribute, $object, $user = null)
    {
        if (!$user instanceof UserInterface) {
            return false;
        }

        $roles = $user->getRoles();
        //        if (in_array($this->getSuperAdminRole(), $roles, true))
//            return true;

        $tokens = explode('.', $attribute);
        $result = in_array(strtoupper($attribute), $roles, true);

        if(!$result && count($tokens) == 2)
        {
            $callbacks = array();
            foreach($roles as $role)
            {
                $lowerCaseRole = strtolower($role);
                $pattern = sprintf('/^%s\.(.+)\.%s$/', $tokens[0], $tokens[1]);

                if(preg_match($pattern, $lowerCaseRole, $matches))
                {
                    $callback = $matches[1].'Vote';
                    if (!method_exists($this, $callback)) {
                        throw new \Exception(sprintf('method %s of class %s not exist', $callback , get_class($this)));
                    }

                    if(call_user_func_array(array($this, $callback), array($attribute, $object, $user)))
                    {
                        return true;
                    }
                }
            }
        }

        return $result;

    }

    public function ownerVote($attribute, $object, $user)
    {
        if(!is_object($object)) return false;

        if (!($object instanceof OwnerInterface)) {
            throw new \Exception(sprintf('%s should implement Knd\Bundle\RadBundle\Security\OwnerInterface', get_class($object)));
        }

        return $object->getOwner() === $user;
    }

}