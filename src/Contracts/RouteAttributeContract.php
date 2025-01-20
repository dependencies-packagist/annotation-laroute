<?php

namespace Annotation\Routing\Contracts;

use Rfc\Request\RequestMethods;

interface RouteAttributeContract extends RequestMethods
{
    public const GET = ['GET', 'HEAD'];
    /**
     * All the verbs supported by the router.
     *
     * @var string[]
     * @see Router::$verbs
     */
    public const ANY = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

}
