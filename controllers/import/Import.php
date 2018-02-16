<?php

use Symfony\Component\DomCrawler\Crawler;


/**
 * Import
 *
 * This module generates JSON files from CDA compilant 
 * files
 *
 * @package PreMyTECD
 */
class Import extends CoreOGraphy\BaseController {
    
    /** @var $file String */
    protected $file = '';
    
    
    /**
     * __construct
     *
     * @param $file String
     *
     */
    public function __construct ($file='cda-sample') {
        
        // Assign files
        $this->file = $file;
        
        
        // Delegate on parent constructor
        parent::__construct ();
        
    }
    
    
    /**
     * handleRequest
     *
     * @package PremyTECD
     */
    public function handleRequest () {
        
        // Set JSON 
        $this->_response = $this->_response->withAddedHeader ('Content-Type', 'application/json; charset=utf-8');
        
        
        /** @var response Array */
        $response = [];
        
        
        /** @var $url String The url path to the xml file */
        $url = FULL_URL . 'assets/' . $this->file . '.xml';
        
        
        /** @var $historial String Stores the file */
        $historial = trim (file_get_contents ($url));
        

        /* @var $crawler Crawler */
        $crawler = new Crawler ($historial);
        $crawler->registerNamespace ('cda', 'urn:hl7-org:v3');
        
        
        // Check validity
        if ('85353-1' != $crawler->filter ('cda|code')->first ()->attr ('code')) {
            $this->_response = $this->_response->withStatus (412);
            $this->_response->getBody ()->write (json_encode (['error' => 'CDA Document is not 85353-1']));
            return;
        }
        
        
        // Identifying client
        $patient = $crawler->filter ('cda|recordTarget cda|patientRole')->first ();
        $response['id_paciente'] = $patient->filter ('cda|id')->attr ('extension');
        
        
        // Identifying measures
        $crawler->filter ('cda|observation')->each (function (Crawler $measure, $i) use (& $response) {
            
            /** @var $measure_id String SNOMED Code */
            $measure_id = $measure->filter ('cda|code')->first ()->attr ('code');
            
            
            /** @var $$measure_name String Measurement */
            $measure_name = $measure->filter ('cda|code')->first ()->attr ('displayName');
            
            
            /** @var $value String Value of the measurement */
            $value = $measure->filter ('cda|value')->first ()->attr ('value');
            
            
            /** @var $time String Moment of the measurement */
            $time = $measure->filter ('cda|effectiveTime')->first ()->attr ('value');
            
            
            
            $key = '';
            $attr = '';
            $state = '';
            
            switch ($measure_id) {
                case '60621009':
                case '301333006':
                case '165109007':
                    $key = 'peso_corporal';
                    break;
                
                case '2.16.840.1.113883.6.96':
                    $key = 'glucosa';
                    break;
                    
                case '271649006':
                case '271650006':
                    $key = 'presion_sanguinea';
                    break;
                    
                case '104847001':
                case '366199006':
                    $key = 'pulso_oxigeno';
                    break;
                    
                case '86290005':
                    $key = 'frecuencia_respiratoria';
                    break;
            }
            
            switch ($measure_id) {
                default:
                case '60621009':
                    $attr = 'imc';
                    break;
                
                case '301333006':
                    $attr = 'peso';
                    break;
                    
                case '165109007':
                    $attr = 'tmb';
                    break;
                    
                case '2.16.840.1.113883.6.96':
                    $attr = 'tmb';
                    break;
                    
                case '271649006':
                    $attr = 'sistolica';
                    break;
                
                case '271650006':                    
                    $attr = 'diastolica';
                    break;
                    
                case '366199006':
                    $attr = 'pulso';
                    break;
                    
                case '104847001':
                    $attr = 'oxigeno';
                    break;
                    
                case '86290005':
                    $attr = 'fr_respiratoria';
                    break;                    

            }
            
            switch ($measure_id) {
                default:
                case '52302001':
                    $state = 'ayuna';
                    break;
                
                case '271063001':
                    $state = 'postpandrial';
                    break;

                case '33747003':
                    $state = '';
                    break;

            }
            
            $response[$key][$time][$attr] = $value;
            if ($state) {
                $response[$key][$time]['estado'] = $state;   
            }

        });
        
        
        // Identifying measures
        $crawler->filter ('cda|substanceAdministration')->each (function (Crawler $substance, $i) use (& $response) {
            
            /** @var $substance_id String RXNormCode */
            $substance_id = $substance->filter ('cda|code')->first ()->attr ('code');
            
            
            /** @var $substance_name String Measurement */
            $substance_name = $substance->filter ('cda|code')->first ()->attr ('displayName');
            
            
            /** @var $time String Moment of the measurement */
            $time = $substance->filter ('cda|effectiveTime')->first ()->attr ('value');
            
            
            /** @var $doses int Quantity */
            $doses = $substance->filter ('cda|doseQuantity')->first ()->attr ('value');
            
            
            $response['registro_medicacion'][$time] = [
                'rxnorm' => $substance_id,
                'cantidad' => $doses
            ];
            
        });
        
        
        
        // Emit response
        $this->_response->getBody ()->write (json_encode ($response, JSON_UNESCAPED_UNICODE ));
        

    }

}
