<?php

namespace App\Snippets;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;

class RefererRouteInfoSnippet
{
    public function __construct(private RequestStack $requestStack, private UrlMatcherInterface $urlMatcher) {}

    public function getRoutingInformationOfReferer(): string
    {
        $referer = (string) $this->requestStack->getMainRequest()->headers->get('referer'); // get the referer, it can be empty!

        if ($referer === '') {

            return '';
        }

        $refererPathInfo = Request::create($referer)->getPathInfo();

        // Remove the scriptname if using a dev controller like app_dev.php (Symfony 3.x only)
        $refererPathInfo = str_replace($this->requestStack->getMainRequest()->getScriptName(), '', $refererPathInfo);

        // try to match the path with the application routing
        $routeInfos = $this->urlMatcher->match($refererPathInfo);

        // get the Symfony route name
        return $refererRoute = $routeInfos['_route'] ?? '';
    }
}
