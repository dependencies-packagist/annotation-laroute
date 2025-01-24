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

    public const whereAlpha        = '[a-zA-Z]+';
    public const whereAlphaNumeric = '[a-zA-Z0-9]+';
    public const whereNumber       = '[0-9]+';
    public const whereUuid         = '[0-7][0-9a-hjkmnp-tv-zA-HJKMNP-TV-Z]{25}';
    public const whereUlid         = '[\da-fA-F]{8}-[\da-fA-F]{4}-[\da-fA-F]{4}-[\da-fA-F]{4}-[\da-fA-F]{12}';

}
