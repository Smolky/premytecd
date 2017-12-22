<?php

/**
 * Export
 *
 * @package PremyTECD
 */
class Export extends CoreOGraphy\BaseController {

    /**
     * handleRequest
     *
     * @package Core-o-Graphy
     */
    public function handleRequest () {
        $this->_response = $this->_response->withAddedHeader ('Content-Type', 'text/xml');
        $this->_response->getBody ()->write ($this->_template->render ('export.xml.twig'));
    }
}
