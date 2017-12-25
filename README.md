# premytecd

Módulo de exportación a Documentos Clínicos de HL7 (CDA) en formato XML con información de pacientes. 

## Arquitectura
Este módulo se encarga de generar documentos compilantes con HL7v3 a partir de ficheros compilados en ASCII plano

Los documentos generados se basan en el uso de la terminología **SNOMED-CT** la cual proporciona vocabularios específicos para conceptos clínicos tales como enfermedades, listas de problemas, alergias, medicaciones y diagnósticos. 


## Introduccion
La historia clínica es el conjunto de documentos que contienen los datos, valoraciones e informaciones de cualquier índole, sobre la situación y la evolución clínica de un paciente a lo largo del proceso asistencial. La historia clínica está constituida por el conjunto de documentos, tanto escritos como gráficos, que hacen referencia a los episodios de salud y enfermedad de una persona, y a la actividad sanitaria que se genera con motivo de esos episodios.


## Conceptos
- **HL7 v3**: La **Arquitectura de Documentos Clínicos (CDA) HL7** es un estándar de marcado de documentos que especifica la estructura y semántica de los documentos clínicos con el objetivo de intercambiar entre proveedores de atención médica y pacientes. CDA se basa en las tecnologías XML, el modelo de referencia de información de HL7 (HL7 RIM), la metodología de HL7 v3 y vocabularios controlados locales. Gracias a esto, es posible generar desde documentos con poca información contextual hasta un documento completamente codificado y referenciado. Un documento CDA se compone por al menos por dos elementos que son, una cabecera que contiene elementos de información que son obligatorios tales como la persona a la que pertenece el documento clínico, y un cuerpo que puede ser un bloque estructurado (structuredBody) o uno no estructurado (nonXMLBody). 

- **Snomed-CT (Systematized Nomenclature of Medicine – Clinical Terms)**: Terminología clínica multilingüeque permite codificar, recuperar, comunicar y analizar datos clínicos permitiendo a los profesionales de la salud representar la información de forma adecuada, precisa e inequívoca.

- **LOINC (Logical Observation Identifiers Names and Codes)**: Base de datos para identificar los resultados de laboratorio médico. LOINC se creó como respuesta a la necesidad de disponer de una base de datos electrónica con información para determinados datos clínicos y está disponible públicamente sin coste alguno. El LOINC utiliza nombres de códigos universales e identificadores para terminología médica relacionada con la Historia clínica electrónica


## Referencias
- http://www.hl7spain.org/documents/comTec/cda/GuiaElementosMinimosCDA.pdf