<?php

/**
 * Export
 *
 * @package PremyTECD
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
        
        // Read file
        $historial = file_get_contents ('assets/' . $this->file . '.json');
        
        
        // Convert to JSON
        $historial = json_decode ($historial, true);
        
        
        // Fill gaps
        foreach (['nombre', 'apellidos', 'genero', 'birth'] as $field) {
            $historial[$field] = isset ($historial[$field]) ? $historial[$field] : '';
        }
        
        
        // For the sake of simplicity we suposse the surnames 
        // can be exploded by spaces
        $data = [
        
            // Codes
            'snomed' => [
                'code' => '2.16.840.1.113883.6.96',
                'name' => 'SNOMED CT'
            ],
            
            
            // Patient information
            'patient' => [
                'id' => $historial['id_paciente'],
                'genre' => $historial['genero'] == 'hombre' ? 'M' : 'F',
                'name' => trim ($historial['nombre'] . ' ' . $historial['apellidos']),
                'birth' => str_replace ('-', '', $historial['f_nacimiento'])
            ],
        ];
        
        
        // CDA
        $data['cda'] = $this->_container['cda'];
        
        
        // Document
        $data['document'] = [
            'id' => rand (1, 10)
        ];
        
        
        // Creation
        $data['cda']['created_at'] = time ();
        
        
        // Init vars
        $data['measures'] = [
            'body_weight' => [],
            'glucose' => [],
            'blood_pressure' => [],
            'pulse' => [],
            'oxygen' => [],
            'temperature' => [],
            'breathing_frequency' => []
        ];
        
        
        // Weight
        foreach ($historial['peso_corporal'] as $record) {
            $data['measures']['body_weight'][] = [
                'time' => str_replace ('-', '', $record['fecha_medición']) . '0000',
                'textual_time' => $record['fecha_medición'],
                'weight' => $record['peso'],
                'bmi' => isset ($record['imc']) ? $record['imc'] : null,
                'bmr' => isset ($record['tmb']) ? $record['tmb'] : null,
            ];
        }
        
        
        // Glocuse
        foreach ($historial['glucosa'] as $record) {
            
            $hour = str_replace (':', '', $record['hora']);
            if (strpos ($hour, 'PM') !== false) {
                $hour = ($hour * 1) + 1200;
            } else {
                $hour = ($hour * 1) + 0;
                
            }
            
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
                'time' => str_replace ('-', '', $record['fecha_medición']) . $hour,
                'textual_time' => $record['fecha_medición'],
                'glucose' => $record['glucosa']
            ];
        }
        
        
        // Blood preassure
        foreach ($historial['presion_sanguinea'] as $record) {
            
            $data['measures']['blood_pressure'][] = [
                'time' => str_replace ('-', '', $record['fecha_medición']),
                'textual_time' => $record['fecha_medición'],
                'systolic' => $record['sistolica'],
                'diastolic' => $record['diastolica'],
            ];
        }
        
        
        // Pulse and oxygen
        foreach ($historial['pulso_oxigeno'] as $record) {
            $data['measures']['pulse'][] = [
                'time' => str_replace ('-', '', $record['fecha_medición']),
                'textual_time' => $record['fecha_medición'],
                'pulse' => $record['pulso']
            ];
            
            $data['measures']['oxygen'][] = [
                'time' => str_replace ('-', '', $record['fecha_medición']),
                'textual_time' => $record['fecha_medición'],
                'oxygen' => $record['oxigeno']
            ];
        }
        
        
        // Breathing frequency
        foreach ($historial['frecuencia_respiratoria'] as $record) {
            $data['measures']['breathing_frequency'][] = [
                'time' => str_replace ('-', '', $record['fecha_medición']),
                'textual_time' => $record['fecha_medición'],
                'breathing_frequency' => $record['fr_respiratoria']
            ];
        }
        
        
        // temperature
        foreach ($historial['temperatura_corporal'] as $record) {
            $data['measures']['temperature'][] = [
                'time' => str_replace ('-', '', $record['fecha_medición']),
                'textual_time' => $record['fecha_medición'],
                'temperature' => $record['temp_corporal']
            ];
        }
        
        
        
        $this->_response = $this->_response->withAddedHeader ('Content-Type', 'text/xml');
        $this->_response->getBody ()->write ($this->_template->render ('export.xml.twig', $data));
    }
}
