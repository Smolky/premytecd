<?php

/**
 * NotFound404
 *
 * @package Core-o-Graphy
 */
class NotFound404 extends CoreOGraphy\BaseController {

    /**
     * handleRequest
     *
     * @package Core-o-Graphy
     */
    public function handleRequest () {
        $this->_response = $this->_response->withStatus (404);
        $this->_response = $this->_response->withAddedHeader ('Content-Type', 'text/xml');
        $this->_response->getBody ()->write ($this->_template->render ('404.html'));
    }
}
