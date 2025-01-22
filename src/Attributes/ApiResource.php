<?php

namespace Annotation\Routing\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class ApiResource extends ResourceAttribute
{
    protected array $resource = [
        'index'   => self::GET,
        'store'   => self::POST,
        'show'    => self::GET,
        'update'  => self::UPDATE,
        'destroy' => self::DELETE,
    ];

}
