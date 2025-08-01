<?php

class Logit extends Database
{
      /*
  * Table logit
  * id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY
  * 
  * 
  *
  * 
  * 
  */
    

  public function __construct()
  {
    parent::__construct();
  }

  public function write($args = [])
  {
    $timestamp = $args['timestamp'];
    $severity = $args['severity'];
    $message = $args['message'];
    $bt = $args['bt'];
    $context = $args['context'];
    $sql = "INSERT INTO logit (timestamp,  severity, message, bt, context) VALUES (:timestamp, :severity, :message, :bt, :context)";
    $stmt = $this->dbcrm()->prepare($sql);
    $stmt->bindParam(':timestamp', $timestamp, PDO::PARAM_STR);
    $stmt->bindParam(':severity', $severity, PDO::PARAM_STR);
    $stmt->bindParam(':message', $message, PDO::PARAM_STR);
    $stmt->bindParam(':bt', $bt, PDO::PARAM_STR);
    $stmt->bindParam(':context', $context, PDO::PARAM_STR);
    $result = $stmt->execute();
    return $result;
      
  }
    
  public function info($message, array $context = [])
  {
    $bt = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
    $this->write([
      'timestamp' => date('m-d-Y H:i:s'),
      'severity' => 'INFO',
      'message' => $message,
      'bt' => $bt,
      'context' => $context
    ]);
  }
   
  public function notice($message, array $context = [])
  {
    $bt = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
    $this->write([
      'timestamp' => date('m-d-Y H:i:s'),
      'severity' => 'NOTICE',
      'message' => $message,
      'bt' => $bt,
      'context' => $context
    ]);
  }

  public function debug($message, array $context = [])
  {
    $bt = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
    $this->write([
      'timestamp' => date('m-d-Y H:i:s'),
      'severity' => 'DEBUG',
      'message' => $message,
      'bt' => $bt,
      'context' => $context
    ]);
  }

  public function warning($message, array $context = [])
  {
    $bt = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
    $this->write([
      'timestamp' => date('m-d-Y H:i:s'),
      'severity' => 'WARNING',
      'message' => $message,
      'bt' => $bt,
      'context' => $context
    ]);
  }

  public function error($message, array $context = [])
  {
    $bt = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
    $this->write([
      'timestamp' => date('m-d-Y H:i:s'),
      'severity' => 'ERROR',
      'message' => $message,
      'bt' => $bt,
      'context' => $context
    ]);
  }

  public function fatal($message, array $context = [])
  {
    $bt = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
    $this->write([
      'timestamp' => date('m-d-Y H:i:s'),
       'severity' => 'FATAL',
      'message' => $message,
      'bt' => $bt,
      'context' => $context
    ]);
  }
}
