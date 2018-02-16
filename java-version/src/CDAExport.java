import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

import org.jtwig.JtwigModel;
import org.jtwig.JtwigTemplate;

import org.json.*;

/**
 * Export
 *
 * This module generates CDA compilant files from
 * middleware json files
 *
 * @package PreMyTECD
 */

public class CDAExport {

	/**
	 * render
	 */
	public String render (String historial_raw) {
		
		/** Temporal variable to store JSON records */
		JSONArray arr;

		
		// Convert the RAW file to a JSON object
		JSONObject historial = new JSONObject (historial_raw);
		
		
		// Get drugs names
		Map<String, String> drugs_names = new HashMap<String, String>();
		Map<String, String> drugs_codes = new HashMap<String, String>();
		
		arr = historial.getJSONArray("nombres_medicamentos");
		for (int i = 0; i < arr.length(); i++) {
			JSONObject record = arr.getJSONObject(i);
			if (record.has ("id_RxNorm")) {
				drugs_names.put(record.getString("id_medicamento"), record.getString("nombre"));
				drugs_codes.put(record.getString("id_medicamento"), record.getString("id_RxNorm"));
			}
		}
		
		
		// Get date of the document
		String documentDate = new SimpleDateFormat("yyyyMMddHHmm").format(new Date());
		
		
		// Retrieve information about the patient
		String patient_name = "";
		if (historial.has("nombre")) {
			patient_name = historial.getString("nombre");
		}
		
		if (historial.has("apellidos")) {
			patient_name = patient_name + " " + historial.getString("apellidos");
		}
		
		
		// Create information about the patient
		HashMap<String, String> patientInfo = new HashMap<String, String>();
		patientInfo.put("id", historial.getString("id_paciente"));
		patientInfo.put("genre", historial.getString("genero") == "hombre" ? "M" : "F");
		patientInfo.put("name", patient_name);
		patientInfo.put("birth", historial.getString("f_nacimiento").replace("-", ""));
		
		
		// Create the template
		JtwigTemplate template = JtwigTemplate.classpathTemplate("./templates/cda.twig");
		
		
		List<Map<String, String>> bodyWeightsMeasures  = new ArrayList<Map<String, String>>();
		List<Map<String, String>> glocoseMeasures  = new ArrayList<Map<String, String>>();
		List<Map<String, String>> bloodPressureMeasures  = new ArrayList<Map<String, String>>();
		List<Map<String, String>> pulseMeasures  = new ArrayList<Map<String, String>>();
		List<Map<String, String>> oxygenMeasures  = new ArrayList<Map<String, String>>();
		List<Map<String, String>> breathingFrequencyMeasures  = new ArrayList<Map<String, String>>();
		List<Map<String, String>> temperatureMeasures  = new ArrayList<Map<String, String>>();
		List<Map<String, String>> substances  = new ArrayList<Map<String, String>>();
		
		
		
		// Body weight measures
		arr = historial.getJSONArray("peso_corporal");
		for (int i = 0; i < arr.length(); i++) {
			HashMap<String, String> bodyWeightInfo = new HashMap<String, String>();
			JSONObject record = arr.getJSONObject(i);
			bodyWeightInfo.put("textual_time", record.getString("fecha_medición"));
			bodyWeightInfo.put("time", this.getCDACompilantTime(record));
			bodyWeightInfo.put("weight", record.has ("peso") ? record.getString("peso") : "");
			bodyWeightInfo.put("bmi", record.has ("imc") ? record.getString("imc") : "");
			bodyWeightInfo.put("bmr", record.has ("tmb") ? record.getString("tmb") : "");
			bodyWeightsMeasures.add(bodyWeightInfo);
		}
		
		
		// Glucose weight measures
		arr = historial.getJSONArray("glucosa");
		for (int i = 0; i < arr.length(); i++) {
			HashMap<String, String> glocoseInfo = new HashMap<String, String>();
			JSONObject record = arr.getJSONObject(i);
			String code = "";
			String name = "";
			
            switch (record.getString ("estado")) {
	            case "ayuna":
	                code = "52302001";
	                name = "glucose-fasting";
	                break;
	            
	            case "postpandrial":
	                code = "271063001";
	                name = "glucose-lunch-time";
	                break;
	            
	            default:
	                code = "33747003";
	                name = "glucose";
	                break;
            
            }			
			
			glocoseInfo.put("textual_time", record.getString("fecha_medición"));
			glocoseInfo.put("time", this.getCDACompilantTime(record));
			glocoseInfo.put("name", name);
			glocoseInfo.put("code", code);
			glocoseMeasures.add(glocoseInfo);
		}

		
		// Blood pressure measures
		arr = historial.getJSONArray("presion_sanguinea");
		for (int i = 0; i < arr.length(); i++) {
			HashMap<String, String> bloodPressureInfo = new HashMap<String, String>();
			JSONObject record = arr.getJSONObject(i);
			bloodPressureInfo.put("textual_time", record.getString("fecha_medición"));
			bloodPressureInfo.put("time", this.getCDACompilantTime(record));
			bloodPressureInfo.put("systolic", record.has ("sistolica") ? record.getString("sistolica") : "");
			bloodPressureInfo.put("diastolic", record.has ("diastolica") ? record.getString("diastolica") : "");
			bloodPressureMeasures.add(bloodPressureInfo);
		}
		

		// Pulse measures
		arr = historial.getJSONArray("pulso_oxigeno");
		for (int i = 0; i < arr.length(); i++) {
			HashMap<String, String> pulseInfo = new HashMap<String, String>();
			JSONObject record = arr.getJSONObject(i);
			pulseInfo.put("textual_time", record.getString("fecha_medición"));
			pulseInfo.put("time", this.getCDACompilantTime(record));
			pulseInfo.put("pulse", record.has ("pulso") ? record.getString("pulso") : "");
			pulseMeasures.add(pulseInfo);
		}
		
		
		// Oxygen measures
		arr = historial.getJSONArray("pulso_oxigeno");
		for (int i = 0; i < arr.length(); i++) {
			HashMap<String, String> OxygenInfo = new HashMap<String, String>();
			JSONObject record = arr.getJSONObject(i);
			OxygenInfo.put("textual_time", record.getString("fecha_medición"));
			OxygenInfo.put("time", this.getCDACompilantTime(record));
			OxygenInfo.put("oxygen", record.has ("oxigeno") ? record.getString("oxigeno") : "");
			oxygenMeasures.add(OxygenInfo);
		}


		// Breathing frequency measures
		arr = historial.getJSONArray("frecuencia_respiratoria");
		for (int i = 0; i < arr.length(); i++) {
			HashMap<String, String> BreathingFrequencyInfo = new HashMap<String, String>();
			JSONObject record = arr.getJSONObject(i);
			BreathingFrequencyInfo.put("textual_time", record.getString("fecha_medición"));
			BreathingFrequencyInfo.put("time", this.getCDACompilantTime(record));
			BreathingFrequencyInfo.put("breathing_frequency", record.has ("fr_respiratoria") ? record.getString("fr_respiratoria") : "");
			breathingFrequencyMeasures.add(BreathingFrequencyInfo);
		}

		
		// Temperature measures
		arr = historial.getJSONArray("temperatura_corporal");
		for (int i = 0; i < arr.length(); i++) {
			HashMap<String, String> temperatureInfo = new HashMap<String, String>();
			JSONObject record = arr.getJSONObject(i);
			temperatureInfo.put("textual_time", record.getString("fecha_medición"));
			temperatureInfo.put("time", this.getCDACompilantTime(record));
			temperatureInfo.put("temperature", record.has ("temp_corporal") ? record.getString("temp_corporal") : "");
			temperatureMeasures.add(temperatureInfo);
		}
		
		
        // Substance administration
		arr = historial.getJSONArray("registro_medicacion");
		for (int i = 0; i < arr.length(); i++) {
			HashMap<String, String> substanceInfo = new HashMap<String, String>();
			JSONObject record = arr.getJSONObject(i);
			substanceInfo.put("textual_time", record.getString("fecha_medición"));
			substanceInfo.put("time", this.getCDACompilantTime(record));
			substanceInfo.put("drug_code", drugs_codes.getOrDefault(record.getString("id_medicamento"), ""));
			substanceInfo.put("drug_name", drugs_names.getOrDefault(record.getString("id_medicamento"), ""));
			substanceInfo.put("doses", record.has ("cantidad") ? record.getString("cantidad") : "1");
			substances.add(substanceInfo);
		}
		
		
		// Assign model
        JtwigModel model = JtwigModel.newModel()
        	
    		/** 
    		 * cda_hl7_root String 
    		 *
    		 * Unique and global identifier composed of an 
    		 * OID or a UUID whose root (root) is assigned by 
    		 * the ISO, or has been obtained from HL7. 
    		 *
    		 * Format: X.Y.Z
    		 * - joint-iso-itu-t:     2 Standard                      <http://oid-info.com/get/2>
    		 * - country:            16 Binds the OID with a country  <http://oid-info.com/get/2.16>
    		 * - area:              840 USA                           <http://oid-info.com/get/2.16.840>
    		 * - organization:        1 American Organization         <http://oid-info.com/get/2.16.840.1>
    		 * - HL7             113883 Health Level 7 (HL7)          <http://oid-info.com/get/2.16.840.1.113883>
    		 * - Examples            19 Not use in real cases         <http://oid-info.com/get/2.16.840.1.113883.19>
    		 */	
        	.with("cda_root", "2.16.840.1.113883.19")
        	.with("cda_author", "2.16.840.1.113883.19")
        	.with("cda_custodian", "2.16.840.1.113883.19")
        	
        	
        	// Document information
        	.with("document_id", "1")
        	.with("cda_created_at", documentDate)
        	
        	
        	// Patient information
        	.with("patient", patientInfo)
        	
        	.with("now", "1982")
        	
        	// Snomed CT information
            // @see https://www.msssi.gob.es/profesionales/hcdsns/areaRecursosSem/snomed-ct/quees.htm        	
        	.with("snomed_code", "2.16.840.1.113883.6.96")
        	.with("snomed_name", "SNOMED CT")
        	
        	
            // RxNorm
            // @see Web Search <https://mor.nlm.nih.gov/RxNav/>
            // @see API <https://rxnav.nlm.nih.gov/REST/rxcui?name=lipitor>
        	.with("rxnorm_code", "2.16.840.1.113883.6.88")
        	.with("rxnorm_name", "RxNorm")
        	
        	
        	.with("body_weight_measures", bodyWeightsMeasures)
        	.with("glucose_measures", glocoseMeasures)
        	.with("blood_pressure_measures", bloodPressureMeasures)
        	.with("pulse_measures", pulseMeasures)
        	.with("oxygen_measures", oxygenMeasures)
        	.with("breathing_frequency_measures", breathingFrequencyMeasures)
        	.with("temperature_measures", temperatureMeasures)
        	
        	.with("substances", substances)
        	
        ;

        return template.render(model);
	
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
     * @see http://www.cdapro.com/know/25001
     *
     * @package PreMyTECD
     */
    private String getCDACompilantTime (JSONObject record) {
        
        /** @var $hour String The default value when hour is not recorded */
        String hour = "0000";

        
        /** @var $$hour_field To reference the key where the hour is stored */
        String hour_field = "";
        
        
        // Determine if the record has some type of hour
        if (record.has ("hora")) {
            hour_field = "hora";
        }
        
        if (record.has ("hora_medicion")) {
            hour_field = "hora_medicion";
        }
        
        
        // Get the hour and translate it to a "hhmm" format
        if (hour_field != "") {
	        hour = record.getString(hour_field).replaceAll(":",  "");
	        if (hour.contains ("PM")) {
	            hour = String.valueOf (Integer.parseInt (hour.replace("PM", "").trim()) + 1200);
	        }
        }
        
        
        // Return the date and the hour
        return record.getString("fecha_medición").replaceAll("-", "" ) + hour;
        
    }	
	
}
