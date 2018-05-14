<?php

/**
 * Export
 *
 * This module generates CDA compilant files from
 * middleware json files
 *
 * @package PreMyTECD
 */
class Export extends CoreOGraphy\BaseController {
    
    /** @var $file String */
    protected $file = '';
    
    
    /**
     * __construct
     *
     * @param $file String
     *
     */
    public function __construct ($file='sample-770') {
        
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
        
        /** @var $url String The url path to the json file */
        $url = FULL_URL . 'assets/' . $this->file . '.json';
        
        
        /** @var $historial String Stores the file */
        $historial = @file_get_contents ($url);
        
        
        // Handle errors
        if (false === $historial) {
            
            /** @var $headers String The headers of the request, to check the failure */
            $headers = get_headers ($url);
            
            
            /** @var $responseCode String the http status code of the request */
            $responseCode = substr ($headers[0], 9, 3);
            
            
            // The resource is not in the system
            if ($responseCode == '404') {
                require_once BASE_DIR . '/controllers/maintenance/NotFound404.php';
                $controller = new NotFound404 ();
                $controller->handle ();
                
            // Any other error
            } else {
                $this->_response = $this->_response->withStatus ($responseCode);
                $this->_response = $this->_response->withAddedHeader ('Content-Type', 'text/xml');
                
            }
            
            // Stop execution
            die ();
            
        }
        
        
        // Get CDA Data
        $cda = $this->process (json_decode ($historial, true));

        
        
        // Return response
        $this->_response = $this->_response->withAddedHeader ('Content-Type', 'text/xml');
        $this->_response->getBody ()->write ($cda);
    
    }
    
    
    /**
     * process
     */
    public function process ($historial) {
        
        
        
        // Retrieve information about drugs
        /** @var $drugs Array */
        $drugs = [];
        if ($historial['nombres_medicamentos']) foreach ($historial['nombres_medicamentos'] as $drug) {
            $drugs[$drug['id_medicamento']] = [
                'id' => $drug['id_medicamento'],
                'category' => $drug['categoria'],
                'name' => $drug['nombre'],
                'family' => $drug['familia'],
                'code' => $this->getRxCode ($drug['nombre'])
            ];
        }
        
        
        
        // Fill gaps
        foreach (['nombre', 'apellidos', 'genero', 'birth'] as $field) {
            $historial[$field] = isset ($historial[$field]) ? $historial[$field] : '';
        }
        

        
        
        
        /** @var $data Array The information to provide to the template */
        $data = [
        
            // Snomed CT information
            // @see https://www.msssi.gob.es/profesionales/hcdsns/areaRecursosSem/snomed-ct/quees.htm
            'snomed' => [
                'code' => '2.16.840.1.113883.6.96',
                'name' => 'SNOMED CT'
            ],
            
            
            // RxNorm
            // @see Web Search <https://mor.nlm.nih.gov/RxNav/>
            // @see API <https://rxnav.nlm.nih.gov/REST/rxcui?name=lipitor>
            'rxnorm' => [
                'code' => '2.16.840.1.113883.6.88', 
                'name' => 'RxNorm'
            ],
            
            // Patient information
            'patient' => [
                'id' => $historial['id_paciente'],
                'genre' => $historial['genero'] == 'hombre' ? 'M' : 'F',
                'name' => trim ($historial['nombre'] . ' ' . $historial['apellidos']),
                'birth' => str_replace ('-', '', $historial['f_nacimiento'])
            ],
        ];
        
        
        // Attach basic CDA information
        $data['cda'] = $this->_container['cda'];
        $data['cda']['created_at'] = time ();
        
        
        // Attachin document information to the document
        // @todo
        $data['document'] = [
            'id' => rand (1, 10)
        ];
        
        

        
        // Init measure vars
        foreach ([
            'body_weight', 'glucose', 'blood_pressure', 
            'pulse', 'oxygen', 'temperature', 'breathing_frequency'
        ] as $key) {
            $data['measures'][$key] = [];
        }
        
        
        // Weight measures
        foreach ($historial['peso_corporal'] as $record) {
            $data['measures']['body_weight'][] = [
                'time' => $this->getCDACompilantTime ($record),
                'textual_time' => $record['fecha_medición'],
                'weight' => $record['peso'],
                'bmi' => isset ($record['imc']) ? $record['imc'] : null,
                'bmr' => isset ($record['tmb']) ? $record['tmb'] : null,
            ];
        }
        
        
        // Glocuse measures
        if (isset ($historial['glucosa'])) foreach ($historial['glucosa'] as $record) {
            
            switch ($record['estado']) {
                case 'ayuna':
                    $code = '52302001';
                    $name = 'glucose-fasting';
                    break;
                
                case 'postpandrial':
                    $code = '271063001';
                    $name = 'glucose-lunch-time';
                    break;
                
                default:
                    $code = '33747003';
                    $name = 'glucose';
                    break;
                
            }
            
            
            $data['measures']['glucose'][] = [
                'code' => $code,
                'name' => $name,
                'time' => $this->getCDACompilantTime ($record),
                'textual_time' => $record['fecha_medición'],
                'glucose' => $record['glucosa']
            ];
        }
        
        
        // Blood preassure measures
        foreach ($historial['presion_sanguinea'] as $record) {
            
            $data['measures']['blood_pressure'][] = [
                'time' => $this->getCDACompilantTime ($record),
                'textual_time' => $record['fecha_medición'],
                'systolic' => $record['sistolica'],
                'diastolic' => $record['diastolica'],
            ];
        }
        
        
        // Pulse and oxygen measures
        foreach ($historial['pulso_oxigeno'] as $record) {
            $data['measures']['pulse'][] = [
                'time' => $this->getCDACompilantTime ($record),
                'textual_time' => $record['fecha_medición'],
                'pulse' => $record['pulso']
            ];
            
            $data['measures']['oxygen'][] = [
                'time' => $this->getCDACompilantTime ($record),
                'textual_time' => $record['fecha_medición'],
                'oxygen' => $record['oxigeno']
            ];
        }
        
        
        // Breathing frequency measures
        foreach ($historial['frecuencia_respiratoria'] as $record) {
            $data['measures']['breathing_frequency'][] = [
                'time' => str_replace ('-', '', $record['fecha_medición']),
                'textual_time' => $record['fecha_medición'],
                'breathing_frequency' => $record['fr_respiratoria']
            ];
        }
        
        
        // temperature measures
        foreach ($historial['temperatura_corporal'] as $record) {
            $data['measures']['temperature'][] = [
                'time' => $this->getCDACompilantTime ($record),
                'textual_time' => $record['fecha_medición'],
                'temperature' => $record['temp_corporal']
            ];
        }
        
        
        // Substance administration
        $data['substances'] = [];
        foreach ($historial['registro_medicacion'] as $record) {
            $data['substances'][] = [
                'time' => $this->getCDACompilantTime ($record),
                'textual_time' => $record['fecha_medición'],
                'drug' => $drugs[$record['id_medicamento']],
                'doses' => $record['cantidad']
            ];
        }
    

        return $this->_template->render ('export.xml.twig', $data);
    
    }
    
    
    
    /**
     * getCDACompilantTime
     *
     * A moment in time is often termed a “timestamp” in CDA. 
     * There are many contexts in which an XML sub-element is added 
     * to “something” in CDA in order to capture the moment in time 
     * when that “something” happened.
     *
     * The moments are a timestamp with the format: YYYYMMDDhhmmss.SSSS±ZZzz
     *
     * @param $record Array a record from the JSON
     *
     * @return String
     *
     * @see http://www.cdapro.com/know/25001
     *
     * @package PreMyTECD
     */
    private function getCDACompilantTime ($record) {
        
        /** @var $hour String The default value when hour is not recorded */
        $hour = "0000";
        
        
        /** @var $hour_field To reference the key where the hour is stored */
        $hour_field = '';
        
        
        // Determine if the record has some type of hour
        if (isset ($record['hora'])) {
            $hour_field = 'hora';
        } elseif (isset ($record['hora_medicion'])) {
            $hour_field = 'hora_medicion';
        }
        
        
        // Get the hour and translate it to a "hhmm" format
        if ($hour_field) {
            $hour = str_replace (':', '', $record[$hour_field]) * 1;
            if (strpos ($hour, 'PM') !== false) {
                $hour = ($hour * 1) + 1200;
            }
        }
        
        
        // Complete
        $hour = str_pad ($hour, 4, "0", STR_PAD_LEFT);
        
        
        // Return the date and the hour
        return str_replace ('-', '', $record['fecha_medición']) . $hour;
        
    }
    
    
    /**
     * getRxCode
     *
     *
     * @package PreMyTECD
     */    
    private function getRxCode ($name) {
        $info = file_get_contents ("https://rxnav.nlm.nih.gov/REST/rxcui?name=" . $name);
        $xml = simplexml_load_string ($info);
        return $xml->idGroup->rxnormId;
    }
    
}
