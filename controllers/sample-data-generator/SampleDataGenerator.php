<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XLSXReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XLSXWriter;


/**
 * SampleDataGenerator
 *
 * @package Premytecd
 */
class SampleDataGenerator extends CoreOGraphy\BaseController {

    /**
     * handleRequest
     *
     * @package Premytecd
     */
    public function handleRequest () {
    
        // Require Export module
        require dirname (__FILE__) . '/../export/Export.php';    
        $export = new Export ();
        
    
        // Set JSON 
        $this->_response = $this->_response->withAddedHeader ('Content-Type', 'application/json; charset=utf-8');
            
    
        /** @var $count int */
        $count = 0;
        
        
        /** @var $response Array */
        $response = [];
        
        
        // First part. Create the patient records
        if (($handle = fopen ('assets/import/base-de-datos-1/tabla-persona.csv', 'r')) !== false) {
    
            // get the first row, which contains the column-titles (if necessary)
            $header = fgetcsv ($handle);

            // loop through the file line-by-line
            while (($data = fgetcsv ($handle)) !== false) {
                
                if ($count > 10) {
                    break;
                }
                    
                // Get cells
                list (
                    $id, $full_name, $name, $surname, $birth_date, $gender, 
                    $civil_state, $phone, $age, $country, 
                    $email, $password, $state
                ) = explode (';', $data[0]);
                
                if ((0 === strpos ($email, 'DOCTOR_')) || (0 === strpos ($email, 'PACIENTE_'))) {
                    continue;
                }
                
                
                $response[$id] = [
                    'apellidos'     => mb_convert_case ($surname, \MB_CASE_TITLE, 'utf-8'),
                    'nombre'        => mb_convert_case ($name, \MB_CASE_TITLE, 'utf-8'),
                    'f_nacimiento'  => date ('Y-m-d', strtotime ($birth_date)),
                    'genero'        => $gender == 'M' ? 'hombre' : 'mujer',
                    'email'         => 'sample-patient-' . $id . '@demo.com',
                    'altura'        => rand (150, 210),
                    
                    'frecuencia_respiratoria'   => [],
                    'glucosa'                   => [],
                    'pulso_oxigeno'             => [],
                    'registro_medicacion'       => [],
                    'presion_sanguinea'         => [],
                    'temperatura_corporal'      => [],
                    'enfermedad_tratamiento'    => [],
                    'peso_corporal'             => [],
                    
                    'id_paciente'               => '',
                    'comentarios'               => [],
                    'nombres_medicamentos'      => []
                    
                ];
                
                
                $count++;
                
                unset ($data);
            }
            fclose($handle);
        }
        
        
        // Second part. Glucose and tall
        if (($handle = fopen ('assets/import/base-de-datos-1/glucosa-y-altura.csv', 'r')) !== false) {
    
            // get the first row, which contains the column-titles (if necessary)
            $header = fgetcsv ($handle);
   

            // loop through the file line-by-line
            while (($data = fgetcsv ($handle)) !== false) {
            
                // Get cells
                list (
                    $id, $creation_date, $void, $insuline, $modification_date, $id_weight, 
                    $id_pressure, $id_choresterol, $tall, $state, $id_comp_exam, $id_peak 
                ) = explode (';', $data[0]);
                
                if ( ! isset ($response[$id])) {
                    continue;
                }

                
                $response[$id]['altura'] = str_replace ('.', '', $tall);
                
                unset ($data);
            }
            fclose($handle);
        }
        
        
        // Third part. Weight
        if (($handle = fopen ('assets/import/base-de-datos-1/pesos.csv', 'r')) !== false) {
    
            // get the first row, which contains the column-titles (if necessary)
            $header = fgetcsv ($handle);
   

            // loop through the file line-by-line
            while (($data = fgetcsv ($handle)) !== false) {
            
                // Get cells
                list (
                    $id_record, $id, $weight, $imc, $tmb, $water_percentage, 
                    $weight_percentage, $dmo, $muscular, $date_creation, $date_modification, $date_unable,
                    $date, $state, $observation
                ) = explode (';', $data[0]);
                
                if ( ! isset ($response[$id])) {
                    continue;
                }

                
                /** @var $date String Get the last date */
                $parts = explode (' ', $date);
                $date = $parts[0];
                $hour = $parts[1];
                
                
                $response[$id]['peso_corporal'][] = [
                    'porcentaje_grasa' => $weight_percentage,
                    'fecha_medición'   => date ('Y-m-d', strtotime ($date)),
                    'hora'             => $hour,
                    'peso'             => $weight,
                    'porcentaje_agua'  => $water_percentage,
                    'tmb'              => $tmb != "NULL" ? $tmb : '',
                    'imc'              => $imc != "NULL" ? $imc : '',
                ];

                
                unset ($data);
            }
            fclose($handle);
        }
        
        
        // Forth. Glucose
        if (($handle = fopen ('assets/import/base-de-datos-1/glucosa.csv', 'r')) !== false) {
    
            // get the first row, which contains the column-titles (if necessary)
            $header = fgetcsv ($handle);
   

            // loop through the file line-by-line
            while (($data = fgetcsv ($handle)) !== false) {
            
                // Get cells
                list (
                    $id_record, $id, $glocuse, $date_creation, $date, $date_modification, 
                    $date_undone, $observation, $state
                ) = explode (';', $data[0]);
                
                if ( ! isset ($response[$id])) {
                    continue;
                }

                
                /** @var $date String Get the last date */
                $parts = explode (' ', $date);
                $date = $parts[0];
                $hour = $parts[1];
                
                
                $response[$id]['glucosa'][] = [
                    'estado'          => 'ayuna',
                    'fecha_medición'  => date ('Y-m-d', strtotime ($date)),
                    'hora'            => $hour,
                    'glucosa'         => $glocuse
                ];

                
                
                unset ($data);
            }
            fclose($handle);
        }
        
        
        // Fifth. Patology
        if (($handle = fopen ('assets/import/base-de-datos-1/patologias.csv', 'r')) !== false) {
    
            // get the first row, which contains the column-titles (if necessary)
            $header = fgetcsv ($handle);
   

            // loop through the file line-by-line
            while (($data = fgetcsv ($handle)) !== false) {
   
                // Get cells
                list (
                    $id, $illness, $id_person, $id_doctor
                ) = explode (';', $data[0]);
                
                if ( ! isset ($response[$id])) {
                    continue;
                }
                
                
                // Discard without diabetes
                if ( ! in_array ($illness, [11, 12])) {
                    continue;
                }

                
                /** @var $date String Get the last date */
                $parts = explode (' ', $date);
                $date = $parts[0];
                $hour = $parts[1];
                
                
                $response[$id]['enfermedad_tratamiento'][] = [
                    'enfermedad_tipo' => $illness == "11" ? 1 : 2,
                    'enfermedad'      => "Diabetes",
                    'hora'            => $hour,
                    'glucosa'         => $glocuse
                ];
                
                
                unset ($data);
            }
            fclose($handle);
        }              
        
        
        $zip = new \ZipArchive ();
        $zip->open ('assets/test.zip',  ZipArchive::CREATE);        
        
        foreach ($response as $index => $patient) {
            $cda = $export->process ($patient);
            $zip->addFromString ('cda-' . $index . '.xml', $cda);
        }
        $zip->close (); 
    
    
        // Emit response
        $this->_response->getBody ()->write (json_encode ($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ));    
        return;
    

    }
}
