<?php

namespace Annotation\Routing\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Fallback extends Route
{
    protected function boot(): void
    {
        $this->fallback = true;
    }

}
