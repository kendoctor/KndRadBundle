<?php
/**
 * Created by PhpStorm.
 * User: kendoctor
 * Date: 15/11/7
 * Time: 下午9:48
 */

namespace Knd\Bundle\RadBundle\Security\Voter;


interface VoterInterface {
    public function getSupportedRoles();
}