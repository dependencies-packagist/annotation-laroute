<?php

namespace Annotation\Routing\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class ApiSingleton extends ResourceAttribute
{
    protected array $resource = [
        'show'    => self::GET,
        'update'  => self::UPDATE,
    ];

}
