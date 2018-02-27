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

    /** @var $file int */
    protected $how_many = '';
    
    
    /** @var $start_date Datetime */
    protected $start_date = null;


    /** @var $end_date String */
    protected $end_date = null;
    

    /**
     * __construct
     *
     * @param $how_many int How many HL7CDA files
     * @param $start_date String since when
     *
     * @package Premytecd
     */
    public function __construct ($how_many = 30, $start_date = null) {
        
        // Assign vars
        $this->start_date = new DateTime ('last day of previous year');
        $this->end_date = clone $this->start_date;
        $this->end_date->add (new DateInterval ('P3M'));
        $this->how_many = $how_many;

        
        // Delegate on parent constructor
        parent::__construct ();
        
    }

    /**
     * handleRequest
     *
     * @package Premytecd
     */
    public function handleRequest () {
    
        /** @var $faker Faker Creates fake data */
        $faker = Faker\Factory::create ('es_ES');
        
        
        /** @var $how_many int How many patients to create */
        $how_many = 30;
        
    
        // Require Export module
        require dirname (__FILE__) . '/../export/Export.php';    
        
        
        /** @var $temp_file String Temporal zip file to download */
        $temp_file = 'assets/test.zip';
        
        
        // Delete if exists
        if (is_file ($temp_file)) {
            @unlink ($temp_file);
        }
        
        
        /** @var $CDAGenerator Export This module will generate HL7 CDA compilant files */
        $CDAGenerator = new Export ();
        
    
        // Set JSON 
        $this->_response = $this->_response->withAddedHeader ('Content-Type', 'application/json; charset=utf-8');
            
    
        /** @var $count int */
        $count = 0;
        
        
        /** @var $response Array */
        $response = [];
        
        
        /** @var $daily_period Array */
        $daily_period = new DatePeriod ($this->start_date, new DateInterval ('P1D'), $this->end_date);
        
        
        /** @var $weekly_period Array */
        $weekly_period = new DatePeriod ($this->start_date, new DateInterval ('P7D'), $this->end_date);
        
        
        
        // First part. Create the patient records
        while ($count <= $this->how_many) {
            
            /** @var $gender Char */
            $gender = $faker->randomElement (['male', 'female']);
            
            
            /** @var $birth_date */
            $birth_date = $faker->dateTimeBetween ('-80 years', '-20 years');
            
            
            /** @var $age int */
            $age = $birth_date->diff (new DateTime('now'))->y;
            
            
            /** @var $height int */
            $height = $faker->biasedNumberBetween (150, 180, 'sqrt');
            
            
            /** @var $has_diabetes */
            $has_diabetes = $faker->biasedNumberBetween (0, 1);
            
            
            // Create patient
            $response[$count] = [
                'id_paciente'   => '',
                'apellidos'     => $faker->lastName,
                'nombre'        => $faker->name ($gender),
                'f_nacimiento'  => $birth_date->format('Y-m-d'),
                'genero'        => $gender == 'male' ? 'hombre' : 'mujer',
                'email'         => 'sample-patient-' . $count . '@demo.com',
                'altura'        => $height,
            ];
            
            
            $response[$count]['nombres_medicamentos'] = [];
            $response[$count]['registro_medicacion'] = [];
            
            
            
            /** @var $ideal_weight */
            $ideal_weight = $height - 100 + ($age / 10) * 0.9;
            
            
            /** @var $ideal_water_percentage */
            $ideal_water_percentage = $faker->numberBetween (
                $gender == 'male' ? 50 : 45, 
                $gender == 'male' ? 65 : 60
            );

            
            /** @var $ideal_fat_percentage */
            $ideal_fat_percentage = $faker->numberBetween (
                $gender == 'male' ? 12 : 50, 
                $gender == 'male' ? 4 : 40
            );
            
            
            /** @var $ideal_imc int */
            $ideal_imc = $ideal_weight / pow (2, ($height / 100));
            
            
            /** @var $ideal_tmb int */
            $ideal_tmb = 1.53 * (
                (10 * $ideal_weight) 
                + (6.25 * $height) 
                - (5 * $age) 
                + ($gender == 'male' ? 5 : -161)
            );
             
             
            // Weight
            // First measure
            $response[$count]['peso_corporal'][] = [
                'fecha_medición'   => $this->start_date->format ('Y-m-d'),
                'hora'             => '',
                'peso'             => $ideal_weight,
                'porcentaje_agua'  => $ideal_water_percentage,
                'porcentaje_grasa' => $ideal_fat_percentage,                    
                'tmb'              => $ideal_tmb,
                'imc'              => $ideal_imc,
            ];
            
            
            // Weight
            foreach ($weekly_period as $index => $date) {
                
                // Skip first step
                if ( ! $index) {
                    continue;
                }
                
                
                /** @var $has_last_measure Array */
                $last_measure = end ($response[$count]['peso_corporal']);
                
                
                /** @var $weight_modification */
                $weight_modification = $faker->numberBetween (-25, 25) / 100;
                
                
                /** @var $direction */
                $direction = $weight_modification > 0 ? 1 : -1;
                
                            
                /** @var $imc int */
                $imc = ($last_measure['peso'] + $weight_modification) / pow (2, ($height / 100));
                
                
                /** @var $tmb int */
                $tmb = 1.53 * (
                    (10 * ($last_measure['peso'] + $weight_modification)) 
                    + (6.25 * $height) 
                    - (5 * $age) 
                    + ($gender == 'male' ? 5 : -161)
                );
                
                
                $response[$count]['peso_corporal'][] = [
                    'fecha_medición'   => $date->format ('Y-m-d'),
                    'hora'             => '',
                    'peso'             => $last_measure['peso'] + $weight_modification,
                    'porcentaje_agua'  => $last_measure['porcentaje_agua'] + $faker->numberBetween (0, 12) / 100 * $direction,
                    'porcentaje_grasa' => $last_measure['porcentaje_grasa'] + $faker->numberBetween (0, 12) / 100 * $direction,
                    'tmb'              => $tmb,
                    'imc'              => $imc,
                ];
                
                
                $response[$count]['frecuencia_respiratoria'][] = [
                    'fecha_medición'   => $date->format ('Y-m-d'),
                    'fr_respiratoria'  => $faker->numberBetween (10, 22),  
                ];
                
                
                $response[$count]['pulso_oxigeno'][] = [
                    'fecha_medición'   => $date->format ('Y-m-d'),
                    'pulso'            => $faker->numberBetween (60, 100),  
                    'oxigeno'          => $faker->numberBetween (95, 100),  
                ];

                
                $response[$count]['presion_sanguinea'][] = [
                    'fecha_medición'   => $date->format ('Y-m-d'),
                    'sistolica'        => $faker->numberBetween (100, 140),  
                    'diastolica'       => $faker->numberBetween (60, 90),  
                ];
                
                $response[$count]['temperatura_corporal'][] = [
                    'fecha_medición'   => $date->format ('Y-m-d'),
                    'temp_corporal'    => $faker->numberBetween (36, 39),  
                ];
            }
            
            
            // Glucose
            if ($has_diabetes) {
                
                $response[$count]['enfermedad_tratamiento'][] = [
                    'enfermedad_tipo' => $faker->biasedNumberBetween (1, 2),
                    'enfermedad'      => 'Diabetes',
                    'fecha_medición'  => $this->start_date->format ('Y-m-d')
                ];

                                
                
                foreach (range (1, 3) as $moment) {
                    
                    /** @var $glucose int */
                    $glucose = $faker->numberBetween ($moment == 1 ? 100 : 140, $moment == 1 ? 125 : 199);
                    
                    
                    /** @var $hour int */
                    $hour = 
                        str_pad (7 * $moment, 2, "1", STR_PAD_LEFT) 
                        . ':' 
                        . str_pad ($faker->numberBetween (0, 59), 2, "0", STR_PAD_LEFT)
                     ;
                    
                    
                    $response[$count]['glucosa'][] = [
                        'estado'          => $moment == 1 ? 'ayuna' : 'postprandial',
                        'fecha_medición'  => $this->start_date->format ('Y-m-d'),
                        'hora'            => $hour,
                        'glucosa'         => $glucose
                    ];
                }
                
            }
            
            
            if ($has_diabetes) foreach ($daily_period as $index => $date) {
                
                // Skip first step
                if ( ! $index) {
                    continue;
                }
                
                
                foreach (range (1, 3) as $moment) {
                    
                    /** @var $has_last_measure Array */
                    $last_measure = array_slice ($response[$count]['glucosa'], -3, 1);;
                    
                    
                    /** @var $hour int */
                    $hour = 
                        str_pad (7 * $moment, 2, "1", STR_PAD_LEFT) 
                        . ':' 
                        . str_pad ($faker->numberBetween (0, 59), 2, "0", STR_PAD_LEFT)
                     ;
                    
                    
                    /** @var $glucose int */
                    $glucose = $faker->numberBetween ($moment == 1 ? 100 : 140, $moment == 1 ? 125 : 199);                    
                    
                    
                    $response[$count]['glucosa'][] = [
                        'estado'          => $moment == 1 ? 'ayuna' : 'postprandial',
                        'fecha_medición'  => $date->format ('Y-m-d'),
                        'hora'            => $hour,
                        'glucosa'         => $glucose
                    ];
                }
                
            }

            
            // go on!
            $count++;
            
        }
        


        $zip = new \ZipArchive ();
        $zip->open ($temp_file,  ZipArchive::CREATE);        
        
        foreach ($response as $index => $patient) {
            $cda = $CDAGenerator->process ($patient);
            $zip->addFromString ('cda-' . $index . '.xml', $cda);
        }
        $zip->close (); 
        
        
        header ("Content-type: application/zip"); 
        header ("Content-Disposition: attachment; filename=$temp_file");
        header ("Content-length: " . filesize ($temp_file));
        header ("Pragma: no-cache"); 
        header ("Expires: 0"); 
        readfile ($temp_file);        
    
    
        // Emit response
        die ();
    

    }
}
