<?php

namespace CoreOGraphy;

use \CoreOGraphy\Item;

/**
 * User
 *
 * This class is an standard representation of an user of the +
 * system
 *
 * @package Core-o-Graphy
 */
class User extends Item {
    
    /**
     * __construct
     *
     * @override
     * 
     * @package Core-o-Graphy
     */
    public function __construct ($id = array ()) {
        parent::__construct ($id, 'users');
    }
    
    
    /**
     * getId
     *
     * @return int
     *
     * @package Core-o-Graphy
     */
    public function getId () {
        return $this->_data['id'];
    }
     
    
    
    /**
     * getEmail
     *
     * @return String
     *
     * @package Core-o-Graphy
     */
    public function getEmail () {
        return $this->_data['email'];
    }
    
    
    /**
     * setEmail
     *
     * @param $email String
     *
     * @package Core-o-Graphy
     */
    public function setEmail ($email) {
        $this->_data['email'] = $email;
    }
    
    
    /**
     * setPassword
     *
     * @param $password String
     *
     * @package Core-o-Graphy
     */
    public function setPassword ($password) {
        $this->_data['password'] = $password;
    } 
}