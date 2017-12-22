# premytecd

Módulo de exportación a Documentos Clínicos de HL7 (CDA) en formato XML con información de pacientes. Los documentos generados integrarán la Nomenclatura Sistematizada de Medicina – Términos Clínicos (SNOMED-CT) la cual proporciona vocabularios específicos para conceptos clínicos tales como enfermedades, listas de problemas, alergias, medicaciones y diagnósticos.

La Arquitectura de Documentos Clínicos (CDA) HL7 es un estándar de marcado de documentos que especifica la estructura y semántica de los documentos clínicos con el objetivo de intercambiar entre proveedores de atención médica y pacientes. CDA se basa en las tecnologías XML, el modelo de referencia de información de HL7 (HL7 RIM), la metodología de HL7 v3 y vocabularios controlados locales. Gracias a esto, es posible generar desde documentos con poca información contextual hasta un documento completamente codificado y referenciado.

Un documento CDA se compone por al menos por dos elementos que son, una cabecera que contiene elementos de información que son obligatorios tales como la persona a la que pertenece el documento clínico, y un cuerpo que puede ser un bloque estructurado (structuredBody) o uno no estructurado (nonXMLBody). 

- **ClinicalDocument**. Representa la raíz del documento el cual contiene uno o más atributos para declaraciones de espacios de nombres (namespaces).
- **effectiveTime**. Indica el momento de la creación del documento. La fecha y tiempo está codificada por HL7 según ISO8601. Así la nomenclatura es año/mes/día/hora/minuto/segundo, y en horario local.
- **confidentialityCode**. Código de confidencialidad. N para confidencialidad normal según una buena práctica de atención sanitaria, R para acceso restringido y V para acceso muy restringido.
- **recordTarget**. Representa la persona a la que pertenece el documento clínico. En este ejemplo, se especifica el nombre del paciente, su género y su fecha de nacimiento.
- **structuredBody**. Representa el cuerpo del documento en el cual se describirán los parámetros obtenidos por el módulo de monitorización de PreMyTECD.


## Aspectos importantes
### OIDs
Para especificar de una forma unívoca el valor de una persona, una organización, un dato codificado, u otra entidad, el CDA permite la utilización de más de un tipo de identificadores, aunque en la mayoría de las implementaciones se utilizarán los OIDs (identificadores ISO admitidos por HL7)

Los identificadores están compuestos por dos partes:
- **root**: Identificador único y global compuesto de un OID o un UUID cuya raíz (root) está asignada por la ISO, o ha sido obtenida desde HL7.
- **extension**: El valor de este atributo es responsabilidad de la organización, sistema o aplicación donde el documento es creado y guardado.

La concatenación de ```root``` y ```extension```, resulta una cadena única y universal que identifica un documento, una persona o una organización.




## Referencias
- http://www.hl7spain.org/documents/comTec/cda/GuiaElementosMinimosCDA.pdf