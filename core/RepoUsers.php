<?php

namespace CoreOGraphy;

use \CoreOGraphy\Repo;

/**
 * RepoUsers
 *
 * @package Core-o-Graphy
 */
class RepoUsers extends Repo {
    
    /** $var _class */
    protected $_class = 'User';
    
    
    /** @var _table */
    protected $_table = 'users';
    
    
    /**
     * getByEmailAndPassword
     *
     * @param $email String
     * @param $password String
     *
     * @return User
     *
     * @package Core-o-Graphy
     */
    
    public function getByEmailAndPassword ($email, $password) {
        
        $results = parent::getAll (1, 1, 'id', 'DESC', ' email = "' . $email . '" AND password = "' . $password . '"');
        
        return reset ($results);
    }
    
    
    /**
     * getByEmail
     *
     * @param $email String
     *
     * @return User
     *
     * @package Core-o-Graphy
     */
    
    public function getByEmail ($email) {
        
        $results = parent::getAll (1, 1, 'id', 'DESC', ' email = "' . $email . '"');
        
        return reset ($results);
    }        
    
}