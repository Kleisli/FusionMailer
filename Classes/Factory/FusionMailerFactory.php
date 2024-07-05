<?php

namespace Kleisli\FusionMailer\Factory;
use GuzzleHttp\Psr7\ServerRequest;
use Kleisli\FusionMailer\Service\FusionMailer;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\ActionResponse;
use Neos\Flow\Mvc\Controller\Arguments;
use Neos\Flow\Mvc\Controller\ControllerContext;
use Neos\Flow\Mvc\Routing\UriBuilder;

/**
 * @Flow\Scope("singleton")
 */
class FusionMailerFactory
{

    public static function createFromPackageKey(string $packageKey): FusionMailer
    {
        $httpRequest = ServerRequest::fromGlobals();

        $actionRequest = ActionRequest::fromHttpRequest($httpRequest);
        $actionRequest->setControllerPackageKey($packageKey);

        $uriBuilder = new UriBuilder();
        $uriBuilder->setRequest($actionRequest);

        $controllerContext = new ControllerContext(
            $actionRequest,
            new ActionResponse(),
            new Arguments([]),
            $uriBuilder
        );
        return self::create($controllerContext, $packageKey);
    }

    public static function create(ControllerContext $controllerContext, ?string $packageKey = null): FusionMailer
    {
        return new FusionMailer($controllerContext, $packageKey);
    }
}
