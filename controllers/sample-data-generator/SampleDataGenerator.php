<?php

/**
 * SampleDataGenerator
 *
 * This controller generates sample data simulating the patient
 * behaviour.
 *
 * Some patients may start with data which reflects issues related to his/her health condition
 * and, combined with the use of the medication results will improve
 *
 * Patients can forget to have their medication
 *
 * @package Premytecd
 */
class SampleDataGenerator extends CoreOGraphy\BaseController {

    /** @var $file int How many HL7 CDA documents will be generated */
    protected $how_many = null;
    
    
    /** @var $start_date Datetime The starting-date where the measures were collected */
    protected $start_date = null;


    /** @var $end_date Datetime The end-date where the measures were collected */
    protected $end_date = null;
    
    
    /** @var $months int The number of months the sample data will be generated */
    protected $months = null;
    

    /**
     * __construct
     *
     * @param $how_many int How many HL7CDA files
     * @param $start_date String The start date of the HL7CDA files
     * @param $months int The number of months where data is been registered
     *
     * @package Premytecd
     */
    public function __construct ($how_many = 30, $start_date = null, $months = 2) {
        
        // Assign vars
        $this->how_many = $how_many;
        $this->months = $months;
        $this->start_date = new DateTime ('last day of previous year');
        
        
        // Calculate the ending date based on the number of months
        $this->end_date = clone $this->start_date;
        $this->end_date->add (new DateInterval ('P' . $months . 'M'));
        
        
        // Delegate on parent constructor
        parent::__construct ();
        
    }
    

    /**
     * handleRequest
     *
     * @package Premytecd
     */
    public function handleRequest () {
    
        /** @var $faker Faker Creates fake-data generator in Spanish */
        $faker = Faker\Factory::create ('es_ES');
        
        
        /** @var $temp_file String Temporal zip file to download */
        $temp_file = 'assets/test.zip';
        
        
        // Delete file if exists
        if (is_file ($temp_file)) {
            @unlink ($temp_file);
        }
        
        
        /** @var $CDAGenerator Export This module will generate HL7 CDA compilant files based on JSONs */
        require dirname (__FILE__) . '/../export/Export.php';    
        $CDAGenerator = new Export ();
        
    
        /** @var $count int This variable counts how many CDA documents are generated */
        $count = 1;
        
        
        /** @var $response Array */
        $response = [];
        
        
        /** @var $daily_period Array A period of each day between the interval */
        $daily_period = new DatePeriod ($this->start_date, new DateInterval ('P1D'), $this->end_date);
        
        
        
        // Creating patients
        while ($count <= $this->how_many) {
            
            /** @var $gender String Determine the gender of the patient with a 50-50 chance */
            $gender = $faker->randomElement (['male', 'female']);
            
            
            /** @var $birth_date int Determine the age of the patient */
            $birth_date = $faker->dateTimeBetween ('-80 years', '-20 years');
            
            
            /** @var $age int Determine the current age (in years) of the patient */
            $age = $birth_date->diff (new DateTime ('now'))->y;
            
            
            /** @var $height int Determine the height of the patient */
            $height = $faker->biasedNumberBetween (150, 180, 'sqrt');
            
            
            /** @var $has_diabetes 3/4 of the patients has diabetes */
            $has_diabetes = $faker->numberBetween (0, 100) > 75;
            
            
            /** @var $has_arterial_hypertension 2/4 of our patients has arterial_hypertension */
            $has_arterial_hypertension = $faker->numberBetween (0, 100) > 50;
            
            
            /** @var $has_hepatitis 1/4 of our patients has hepatitis */
            $has_hepatitis = $faker->numberBetween (0, 100) > 25;
            
            
            /** 
             * @var $weight The weight of the patient is calculated based on its age and height 
             *
             * Patients with illness have more chance to overweight
             */
            $weight = ($height - 100 + ($age / 10) * 0.9) + $faker->numberBetween (-20, 50) / 100;
            
            if ($has_diabetes || $has_arterial_hypertension) {
                $weight += $faker->numberBetween (0, 50) / 100;
            }
            
            
            /** @var $imc int */
            $imc = $this->getIMC ($weight, $height);
            
            
            /** @var $tmb int */
            $tmb = $this->getTMB ($weight, $height, $age, $gender);
            
            
            /** 
             * @var $water_percentage int The water percentage varies between males and females 
             * 
             * By weight, the average human adult male is approximately 60% water and 
             * the average adult female is approximately 50%.
             *
             * @link https://en.wikipedia.org/wiki/Body_water
             */
            $water_percentage = $faker->numberBetween (
                $gender == 'male' ? 50 : 45, 
                $gender == 'male' ? 65 : 60
            );

            
            /** 
             * @var $fat_percentage int 
             * 
             * @link https://en.wikipedia.org/wiki/Body_fat_percentage#From_BMI
             */
            $fat_percentage = (1.20 * $imc) + (.23 * $age) - (10.8 * ($gender == 'male')) - 5.4;
            
            
            /** @var $patient_base_data Array The base-patient info */
            $patient_base_data = [
                'id_paciente'   => '',
                'apellidos'     => $faker->lastName,
                'nombre'        => $faker->name ($gender),
                'f_nacimiento'  => $birth_date->format ('Y-m-d'),
                'genero'        => $gender == 'male' ? 'hombre' : 'mujer',
                'email'         => 'sample-patient-' . $count . '@demo.com',
                'altura'        => $height
            ];
            
            
            // Create the response of this patient 
            $response[$count] = $patient_base_data;
            $response[$count]['nombres_medicamentos'] = [];
            $response[$count]['registro_medicacion'] = [];
             
             
            // First measure of weight. 
            // This measure is used to be base of futher measurements
            $response[$count]['peso_corporal'][] = [
                'fecha_medición'   => $this->start_date->format ('Y-m-d'),
                'peso'             => $weight,
                'porcentaje_agua'  => $water_percentage,
                'porcentaje_grasa' => $fat_percentage,                    
                'tmb'              => $tmb,
                'imc'              => $imc,
            ];
            
            
            // Register the illness when the patient was recorded
            if ($has_diabetes) {
                $response[$count]['enfermedad_tratamiento'][] = [
                    'enfermedad_tipo' => $faker->biasedNumberBetween (1, 2),
                    'enfermedad'      => 'Diabetes',
                    'fecha_medición'  => $this->start_date->format ('Y-m-d'),
                    'hora_medición'   => $this->getRandomHour ($faker)
                ];
            }
            
            if ($has_arterial_hypertension) {
                $response[$count]['enfermedad_tratamiento'][] = [
                    'enfermedad'      => 'Hipertensión Arterial',
                    'fecha_medición'  => $this->start_date->format ('Y-m-d'),
                    'hora_medición'   => $this->getRandomHour ($faker)
                ];
            }
            
            if ($has_arterial_hypertension) {
                $response[$count]['enfermedad_tratamiento'][] = [
                    'enfermedad_tipo' => $faker->biasedNumberBetween (1, 2) == 1 ? 'C' : 'A',
                    'enfermedad'      => 'Hepatitis',
                    'fecha_medición'  => $this->start_date->format ('Y-m-d'),
                    'hora_medición'   => $this->getRandomHour ($faker)
                ];
            }

            
            // Regular measures
            // As time pass by, users get measures more regularly
            foreach ($daily_period as $index => $date) {
                
                // Skip first step. It has already done in the first measure of weight
                if ( ! $index) {
                    continue;
                }
                
                
                /** @var $has_glocuse_measures_this_day Boolean */
                $has_glocuse_measures_this_day = $has_diabetes && $faker->numberBetween ($index, 100) > 75;
                
                
                /** @var $has_body_weight_measures_this_day Boolean */
                $has_body_weight_measures_this_day = $faker->numberBetween ($index, 100) > 75;
                
                
                /** @var $has_breathing_frequency_measures_this_day Boolean */
                $has_breathing_frequency_measures_this_day = $faker->numberBetween ($index, 100) > 44;
                
                
                /** @var $has_pulse_measures_this_day Boolean */
                $has_pulse_measures_this_day = $faker->numberBetween ($index, 100) > 56;
                
                
                /** @var $has_blood_pressure_measures_this_day Boolean */
                $has_blood_pressure_measures_this_day = $faker->numberBetween ($index, 100) > 33;
                
                
                /** @var $has_body_temperature_measures_this_day Boolean */
                $has_body_temperature_measures_this_day = $faker->numberBetween ($index, 100) > 50;
                
                
                // Body measures
                if ($has_body_weight_measures_this_day) {
                    
                    /** @var $last_measure Array */
                    $last_measure = end ($response[$count]['peso_corporal']);
                    
                    
                    /** @var $weight_modification int As time goes by, users get more stable weight */
                    $weight_modification = $faker->biasedNumberBetween (-25, 25, function ($x) use ($index) {
                        return 1 - ($x);
                    }) / 100;
                    
                    
                    /** @var $direction int Determine if the conditions are positive or negative */
                    $direction = $weight_modification > 0 ? 1 : -1;
                    

                    /** @var $imc int */
                    $imc = $this->getIMC ($last_measure['peso'] + $weight_modification, $height);
                    
                    
                    /** @var $tmb int */
                    $tmb = $this->getTMB ($last_measure['peso'] + $weight_modification, $height, $age, $gender);                
                    
                    
                    // Add the measure
                    $response[$count]['peso_corporal'][] = [
                        'fecha_medición'   => $date->format ('Y-m-d'),
                        'hora'             => '',
                        'peso'             => $last_measure['peso'] + $weight_modification,
                        'porcentaje_agua'  => $last_measure['porcentaje_agua'] + $faker->numberBetween (0, 12) / 100 * $direction,
                        'porcentaje_grasa' => $last_measure['porcentaje_grasa'] + $faker->numberBetween (0, 12) / 100 * $direction,
                        'tmb'              => $tmb,
                        'imc'              => $imc,
                        'hora_medición'    => $this->getRandomHour ($faker)
                    ];
                }
                
                
                // Add the breathing frequency
                if ($has_breathing_frequency_measures_this_day) {
                    $response[$count]['frecuencia_respiratoria'][] = [
                        'fecha_medición'   => $date->format ('Y-m-d'),
                        'hora_medición'    => $this->getRandomHour ($faker),
                        'fr_respiratoria'  => $faker->numberBetween (10, 22),  
                    ];
                }
                
                
                // Add pulse and oxygen measures
                if ($has_pulse_measures_this_day) {
                    $response[$count]['pulso_oxigeno'][] = [
                        'fecha_medición'   => $date->format ('Y-m-d'),
                        'hora_medición'    => $this->getRandomHour ($faker),
                        'pulso'            => $faker->numberBetween (60, 100),  
                        'oxigeno'          => $faker->numberBetween (95, 100),
                    ];
                }

                
                // Add blood pressure
                if ($has_blood_pressure_measures_this_day) {
                    $response[$count]['presion_sanguinea'][] = [
                        'fecha_medición'   => $date->format ('Y-m-d'),
                        'hora_medición'    => $this->getRandomHour ($faker),
                        'sistolica'        => $faker->numberBetween (100, 140),  
                        'diastolica'       => $faker->numberBetween (60, 90)
                    ];
                }
                
                
                // Add body temperature
                if ($has_body_temperature_measures_this_day) {
                    $response[$count]['temperatura_corporal'][] = [
                        'fecha_medición'   => $date->format ('Y-m-d'),
                        'hora_medición'    => $this->getRandomHour ($faker),
                        'temp_corporal'    => $faker->biasedNumberBetween (36, 39, function ($x) {
                            return 1 - $x;
                        })
                    ];
                }
                
                
                // Register glucose measurements from patients with diabetes
                if ($has_glocuse_measures_this_day) {
                        
                    // Patients with diabetes should measure its glucose three times at day
                    foreach (range (1, 3) as $moment) {
                        
                        /** @var $glucose int */
                        $glucose = $faker->numberBetween ($moment == 1 ? 100 : 140, $moment == 1 ? 125 : 199);
                        
                        
                        /** @var $hour String */
                        $hour = $this->getRandomHour ($faker, 7 * $moment);
                        
                        
                        // Attach the measurement
                        $response[$count]['glucosa'][] = [
                            'estado'          => $moment == 1 ? 'ayuna' : 'postprandial',
                            'fecha_medición'  => $date->format ('Y-m-d'),
                            'hora'            => $hour,
                            'glucosa'         => $glucose
                        ];
                    }
                }                
                
                
            }
            
            
            // go on!
            $count++;
            
        }



        /** @var $zip Create the ZIP container */
        $zip = new \ZipArchive ();
        
        
        // Create the file
        $zip->open ($temp_file,  ZipArchive::CREATE);        
        
        
        // Generate the CDA files from JSON
        foreach ($response as $index => $patient) {
            $cda = $CDAGenerator->process ($patient);
            $zip->addFromString ('cda-' . $index . '.xml', $cda);
        }
        $zip->close (); 
        
        
        // Return the response
        header ("Content-type: application/zip"); 
        header ("Content-Disposition: attachment; filename=$temp_file");
        header ("Content-length: " . filesize ($temp_file));
        header ("Pragma: no-cache"); 
        header ("Expires: 0"); 
        readfile ($temp_file);        
    
    
        // Emit response
        die ();
    

    }
    
    
    /**
     * getIMC
     *
     * @param $weight float
     * @param $height int
     *
     * @return float
     */
    private function getIMC ($weight, $height) {
        return $weight / pow (2, ($height / 100));
    }
    
    
    /**
     * getIMC
     *
     * @param $weight float
     * @param $height int
     * @param $age int
     * @param $gender String
     *
     * @return float
     */    
    private function getTMB ($weight, $height, $age, $gender) {
        return 1.53 * ((10 * $weight) + (6.25 * $height) - (5 * $age) + ($gender == 'male' ? 5 : -161));
    }
    
    
    /**
     * getRandomHour
     *
     * Returns a random 
     *
     * @param $faker Faker
     * @param $hour int|null
     *
     * @return String
     */       
    private function getRandomHour ($faker, $hour = null) {
        return  
            str_pad ($hour ? $hour : $faker->numberBetween (7, 23), 2, "1", STR_PAD_LEFT) 
            . ':' 
            . str_pad ($faker->numberBetween (0, 59), 2, "0", STR_PAD_LEFT)
         ;    
    }
            

}
