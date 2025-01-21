<?php

namespace Annotation\Routing\Attributes\Contracts;

use Rfc\Request\RequestMethods;

interface RouteAttributeContract extends RequestMethods
{
    public const GET    = ['GET', 'HEAD'];
    public const UPDATE = ['PUT', 'PATCH'];
    /**
     * All the verbs supported by the router.
     *
     * @var string[]
     * @see Router::$verbs
     */
    public const ANY = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

}
