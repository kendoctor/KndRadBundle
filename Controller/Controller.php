<?php

namespace Knd\Bundle\RadBundle\Controller;

use Knd\Bundle\RadBundle\Form\FormManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class Controller
 * @package Knd\Bundle\RadBundle\Controller
 */
class Controller extends BaseController
{
    /**
     * @return FormManager
     */
    protected function getFormManger()
    {
        return $this->get('knd_rad.form.manager');
    }

    /**
     * @return RouterInterface
     */
    protected function getRouter()
    {
        return $this->get('router');
    }

    /**
     * @param string $route
     * @param array $parameters
     * @param int $status
     * @return RedirectResponse
     */
    protected function redirectToRoute($route, array $parameters = array(), $status = 302)
    {
        return new RedirectResponse($this->getRouter()->generate($route, $parameters), $status);
    }

    /**
     * @param mixed $attributes
     * @param null $object
     */
    protected function isGranted($attributes, $object = null)
    {
    }

    /**
     * @param $object
     * @param array $criteria
     */
    protected function isGrantedOr403($object, $criteria = array())
    {
    }

    /**
     * @param string $type
     * @param string $message
     * @return mixed
     */
    protected function addFlash($type, $message)
    {
        return $this->getFlashBag()->add($type, $message);
    }

    /**
     * @param $object
     * @param null $purpose
     * @param array $options
     * @return \Symfony\Component\Form\FormInterface
     */
    protected function createObjectForm($object, $purpose = null, array $options = array())
    {
        return $this->getFormManger()->createObjectForm($object, $purpose, $options);
    }

    /**
     * @param $object
     * @param null $purpose
     * @param array $options
     * @return \Symfony\Component\Form\FormInterface
     */
    protected function createBoundObjectForm($object, $purpose = null, array $options = array())
    {
        return $this->getFormManger()->createBoundObjectForm($object, $purpose, $options);
    }

    /**
     * @return Session
     */
    protected function getSession()
    {
        return $this->get('session');
    }

    /**
     * @return object
     */
    protected function getMailer()
    {
        return $this->get('mailer');
    }

    /**
     * @return object
     */
    protected function getSecurity()
    {
        return $this->get('security.context');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface|\Symfony\Component\HttpFoundation\Session\SessionBagInterface
     */
    protected function getFlashBag()
    {
        return $this->getSession()->getFlashBag();
    }

    /**
     * @param string $name
     * @return mixed
     */
    protected function getParameter($name)
    {
        return $this->container->getParameter($name);
    }
}
