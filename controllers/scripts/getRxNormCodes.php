<?php

/**
 * getRxNormCodes
 *
 * This module gets the RXNorm codes from a list of 
 * drugs
 *
 * @package PreMyTECD
 */
class getRxNormCodes extends CoreOGraphy\BaseController {
    
    /** @var $files Array */
    protected $files = [
        'https://docs.google.com/spreadsheets/d/e/2PACX-1vQlJXXr33y8iQK6lZPx7TqFFTER0QN2cY9ThYOl3KLRPNxNhYpWzVNylPgebnUoS0RkxObDrartk9mE/pub?gid=0&single=true&output=csv',
        'https://docs.google.com/spreadsheets/d/e/2PACX-1vQlJXXr33y8iQK6lZPx7TqFFTER0QN2cY9ThYOl3KLRPNxNhYpWzVNylPgebnUoS0RkxObDrartk9mE/pub?gid=483186525&single=true&output=csv',
        'https://docs.google.com/spreadsheets/d/e/2PACX-1vQlJXXr33y8iQK6lZPx7TqFFTER0QN2cY9ThYOl3KLRPNxNhYpWzVNylPgebnUoS0RkxObDrartk9mE/pub?gid=169268412&single=true&output=csv'
        
    ];
    
    
    /**
     * handleRequest
     *
     * @package PremyTECD
     */
    public function handleRequest () {
        
        /** @var $data Array */
        $data = [];
        
        
        // Get the files of each field
        foreach ($this->files as $file) {
            
            /** @var $content String get the content of the CSV file */
            $content = file_get_contents ($file);
            
            
            // Get every row
            foreach (explode ("\n", $content) as $index => $line) {
                
                // Skip title
                if (0 === $index) {
                    continue;
                }
                
                
                /** @var $fields Array get the fields of each row */
                $fields = explode (",", trim ($line));
                
                
                /** @var $drug String */
                $drug = end ($fields);
                
                
                // Skip empty
                if ( ! $drug) {
                    continue;
                }
                
                
                // Get the right element
                $drug = prev ($fields);
                
                
                // Store
                $data['drugs'][$drug] = $this->getRxCode ($drug);
                
            }
            
        }
        

        // Return response
        $this->_response->getBody ()->write ($this->_template->render ('rxcodes.html.twig', $data));
    }
    
    
    
    
    /**
     * getRxCode
     *
     *
     * @package PreMyTECD
     */    
    private function getRxCode ($name) {
        $info = file_get_contents ("https://rxnav.nlm.nih.gov/REST/rxcui?name=" . urlencode ($name));
        $xml = simplexml_load_string ($info);
        return $xml->idGroup->rxnormId;
    }
    
}
