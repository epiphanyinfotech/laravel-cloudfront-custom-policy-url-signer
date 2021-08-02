<?php

namespace EpiphanyInfotech\CustomPolicyUrl;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Aws\CustomPolicyUrl\Skeleton\SkeletonClass
 */
class CustomPolicyUrlFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'custom-policy-url';
    }
}
