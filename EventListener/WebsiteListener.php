<?php
/**
 * This file is part of the Presta Bundle project.
 *
 * (c) Nicolas Bastien <nbastien@prestaconcept.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Presta\CMSCoreBundle\EventListener;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpKernel\Kernel;
use Presta\CMSCoreBundle\Model\WebsiteManager;

/**
 * Handle website selection based on request
 *
 * @package    PrestaCMS
 * @subpackage CoreBundle
 * @author     Nicolas Bastien <nbastien@prestaconcept.net>
 */
class WebsiteListener
{
    /**
     * @var WebsiteManager
     */
    protected $websiteManager;

    /**
     * @var string
     */
    protected $environment;

    /**
     * @param WebsiteManager $websiteManager
     * @param Kernel $kernel
     */
    public function __construct(WebsiteManager $websiteManager, Kernel $kernel)
    {
        $this->websiteManager = $websiteManager;
        $this->environment = $kernel->getEnvironment();
    }

    /**
     * Load current website on front
     *
     * @param \Symfony\Component\EventDispatcher\Event $event
     */
    public function onKernelRequest(Event $event)
    {
        $request = $event->getRequest();

        if (strpos($request->getPathInfo(), '/_wdt') === 0
            || strpos($request->getPathInfo(), '/robots.txt') === 0 || strpos($request->getPathInfo(), '/css') === 0
            || strpos($request->getPathInfo(), '/js') === 0) {
            //For Front only
            return;
        }

        if (strpos($request->getPathInfo(), '/admin') === 0) {
            if ($this->websiteManager->getCurrentWebsite() != null) {
                //Website is already loaded : this listener is triggered by every subrequests like {% render url() %}
                return;
            }
            //Administration
            $websiteId = $request->get('website', null);
            $locale = $request->get('locale', null);
            $this->websiteManager->loadWebsiteById($websiteId, $locale, $this->environment);
        } else {
            //Front case
            //Load current website
            $website = $this->websiteManager->loadWebsiteByHost($request->getHost());
            $request->setLocale($website->getLocale());
        }
    }
}
