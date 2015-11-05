<?php

namespace Knd\Bundle\RadBundle\EventListener;

use Knd\Bundle\RadBundle\View\NameDeducer;
use Knd\Bundle\RadBundle\View\NameDeducer\NoControllerNameException;
use Knd\Bundle\RadBundle\View\NameDeducer\NotInBundleException;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\EngineInterface;

/**
 * Adds Response event listener to render no-Response
 * controller results (arrays).
 */
class ViewListener
{
    private $templating;
    private $viewNameDeducer;

    /**
     * Initializes listener.
     *
     * @param EngineInterface      $templating         Templating engine
     * @param NameDeducer      $viewNameDeducer    Deduces the view name from controller name
     */
    public function __construct(EngineInterface $templating, NameDeducer $viewNameDeducer)
    {
        $this->templating = $templating;
        $this->viewNameDeducer = $viewNameDeducer;
    }

    /**
     * Patches response on empty responses.
     *
     * @param GetResponseForControllerResultEvent $event Event instance
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $request = $event->getRequest();

        try {
            $viewName = $this->viewNameDeducer->deduce($request);
        }
        catch (NotInBundleException $e) {
            return;
        }
        catch (NoControllerNameException $e) {
            return;
        }

        $viewParams = $event->getControllerResult();
        if (!is_array($viewParams) && !is_null($viewParams)) {
            return;
        }

        if ($this->templating->exists($viewName)) {
            $response = $this->templating->renderResponse($viewName, $viewParams ?: array());
            $event->setResponse($response);
        }
        else
        {
            throw new \Exception($viewName);
        }

    }
}
