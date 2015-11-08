<?php
/**
 * Created by PhpStorm.
 * User: kendoctor
 * Date: 15/11/7
 * Time: 下午9:30
 */

namespace Knd\Bundle\RadBundle\Security\Voter;


class VoterStack {
    private $voters;

    public function __construct(array $voters = array())
    {
        $this->voters = $voters;
    }

    public function hasVoter(VoterInterface $voter)
    {
        return in_array($voter, $this->voters, true);
    }

    public function addVoter(VoterInterface $voter)
    {
        if (!$this->hasVoter($voter)) {
            $this->voters[] = $voter;
        }
    }


    public function getVoters()
    {
        return $this->voters;
    }


    public function getRoles()
    {
        $roles = array();

        foreach ($this->voters as $voter) {
            $roles = array_merge($roles, $voter->getSupportedRoles());
        }

        return $roles;
    }

}