<?php

/**
 * Config file
 *
 * @package premyTECD
 */
 
/** $production boolean  Defines if the application is 
                         in production */
$production = false;


/** $base_url String Defines the base_path of the application */
$base_url = '//';


/** $dsn String Database connection string */
$dsn = 'mysql:host=localhost;dbname=;charset=utf8mb4';


/** $user String Database user */
$user='';


/** $password String Database password */
$password='';


/** $email_server String The email server */
$email_server = '';


/** $email_port String The port of the email server */
$email_port = '';


/** $email_protocol String The protocol of the mail server*/
$email_protocol = 'tls';


/** $email_username String The user of the email account*/
$email_username = '';


/** $email_password String The password of the email account*/
$email_password = '';


/** $email_from String The 'from' account */
$email_from = '';


/** 
 * $cda_hl7_root String 
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
$cda_hl7_root = '2.16.840.1.113883.19';


/** $cda_hl7_author String The author of the document */
$cda_hl7_author = '2.16.840.1.113883.19';


/** $cda_hl7_custodian String The custodian of the document */
$cda_hl7_custodian = '2.16.840.1.113883.19';