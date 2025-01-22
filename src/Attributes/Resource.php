<?php

namespace Annotation\Routing\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Resource extends ResourceAttribute
{
    protected array $resource = [
        'index'   => self::GET,
        'create'  => self::GET,
        'store'   => self::POST,
        'show'    => self::GET,
        'edit'    => self::GET,
        'update'  => self::UPDATE,
        'destroy' => self::DELETE,
    ];

}
