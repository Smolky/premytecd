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
     * @package PremyTECD
     */
    public function handleRequest () {
        
        // Read file
        $historial = file_get_contents ('assets/sample.json');
        
        
        // Convert to JSON
        $historial = json_decode ($historial, true);
        
        
        // Create data
        $surname = explode (' ', $historial['apellidos']);
        
        
        // For the sake of simplicity we suposse the surnames 
        // can be exploded by spaces
        $data = [
        
            // Patient information
            'patient' => [
                'name' => $historial['nombre'],
                'surname1' => reset ($surname),
                'surname2' => end ($surname),
                'birth' => str_replace ('-', '', $historial['f_nacimiento'])
            ],
        ];
        
        
        // Init vars
        $data['measures']['body_weight'] = [];
        $data['measures']['glucose'] = [];
        $data['measures']['blood_pressure'] = [];
        $data['measures']['pulse'] = [];
        $data['measures']['oxygen'] = [];
        
        
        // Weight
        foreach ($historial['peso_corporal'] as $record) {
            $data['measures']['body_weight'][] = [
                'time' => str_replace ('-', '', $record['fecha_medición']) . '0000',
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
                'glucose' => $record['glucosa']
            ];
        }
        
        
        // Blood preassure
        foreach ($historial['presion_sanguinea'] as $record) {
            
            $data['measures']['blood_pressure'][] = [
                'time' => str_replace ('-', '', $record['fecha_medición']),
                'sistolic' => $record['sistolica'],
                'diastolic' => $record['diastolica'],
            ];
        }
        
        
        // Pulse
        foreach ($historial['pulso_oxigeno'] as $record) {
            $data['measures']['pulse'][] = [
                'time' => str_replace ('-', '', $record['fecha_medición']),
                'pulse' => $record['pulso']
            ];
            
            $data['measures']['oxygen'][] = [
                'time' => str_replace ('-', '', $record['fecha_medición']),
                'oxygen' => $record['oxigeno']
            ];
        }
        
        
        $this->_response = $this->_response->withAddedHeader ('Content-Type', 'text/xml');
        $this->_response->getBody ()->write ($this->_template->render ('export.xml.twig', $data));
    }
}
