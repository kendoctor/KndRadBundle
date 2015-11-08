<?php
/**
 * Created by PhpStorm.
 * User: kendoctor
 * Date: 15/11/7
 * Time: 下午7:53
 */

namespace Knd\Bundle\RadBundle\Security\Voter;


class VoterFactory {

    public function create($class)
    {
        return new Voter($class);
    }
}