<?php
/**
 * Created by PhpStorm.
 * User: kendoctor
 * Date: 15/11/7
 * Time: 下午6:40
 */

namespace Knd\Bundle\RadBundle\Security\Voter;


class Voter extends AbstractVoter {

    public function getSupportedRoles()
    {
        return array(
            $this->newRole('index'),
            $this->newRole('new'),
            $this->newRole('edit'),
            $this->newRole('delete')
        );
    }

}