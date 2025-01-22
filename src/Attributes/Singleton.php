<?php

namespace Annotation\Routing\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Singleton extends ResourceAttribute
{
    protected array $resource = [
        'show'   => self::GET,
        'edit'   => self::GET,
        'update' => self::UPDATE,
    ];

}
