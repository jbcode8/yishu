<?php
    /**
     * 根据uid获取用户名
     * @param integer $uid 用户uid
     * @return string      用户名
     */
    function getUsername($uid)
    {
        return  M('ucenterMember')->where(array('id'=>$uid))->getField('username');
    }

    /**
     * 根据用户名获取uid
     * @param string $username 用户用户名
     * @return int      用户uid
     */
    function getUid($username)
    {
        return  M('UcenterMember')->where(array('username'=>$username))->getField('id');
    }

