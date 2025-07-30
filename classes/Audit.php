<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

class Audit extends Database
{
  public function __construct()
  {
    parent::__construct();
  }

  // <i class="fa-brands fa-watchman-monitoring"></i>


  public function log($user_id, $event, $resource, $useragent, $ip, $location, $data)
  {
    $sql = "INSERT INTO audit (user_id, event, resource, ip, useragent, location, data) VALUES (:user_id, :event, :resource, :ip, :usergent, :location, :data)";
    $stmt = $this->dbadmin()->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':event', $event, PDO::PARAM_STR);
    $stmt->bindParam(':resource', $resource, PDO::PARAM_STR);
    $stmt->bindParam(':ip', $ip, PDO::PARAM_STR);
    $stmt->bindParam(':usergent', $useragent, PDO::PARAM_STR);
    $stmt->bindParam(':location', $location, PDO::PARAM_INT);
    $stmt->bindParam(':data', $data, PDO::PARAM_STR);
    $stmt->execute();
  }


  /**
   * Get All Logs By User ID.
   *
   * @param int $user_id
   */
  public function user_log($user_id)
  {
    $sql = "SELECT * FROM audit WHERE user_id = :user_id";
    $stmt = $this->dbadmin()->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll();
    return $result;
  }

  /**
   * Get Logs Count By User ID.
   *
   * @param int $user_id
   *
   * 
   */
  public function countLogsByUser($user_id)
  {
  }


  /**
   * Get All Logs By Resource.
   *
   * @param string $resource
   *
   * 
   */
  public function resource_log($resource)
  {
    $sql = "SELECT * FROM audit WHERE resource = :resource";
    $stmt = $this->dbadmin()->prepare($sql);
    $stmt->bindParam(':resource', $resource, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll();
    return $result;
  }


  /**
   * Get Logs Count By Resource.
   *
   * @param string $resource
   *
   * @return int
   */
  public function countLogsByResource($resource)
  {
  }

  /**
   * Get All Logs By User and Events.
   *
   * @param int $user_id
   * @param array $events
   *
   * 
   */
  public function user_and_eventlog($user_id, $events)
  {
    $sql = "SELECT * FROM audit WHERE user_id = :user_id AND event IN (:events)";
    $stmt = $this->dbadmin()->prepare($sql);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':events', $events, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll();
    return $result;
  }
  /**
   * Get Logs Count By User and Events.
   *
   * @param int $user_id
   * @param array $events
   *
   * 
   */
  public function countLogsByUserAndEvents($user_id, array $events)
  {
  }

  /**
   * Get All Logs By Resource and Events.
   *
   * @param string $resource
   * @param array $events
   * 
   *
   * 
   *
   * 
   */
  public function resource_and_events_log($resource, $events)
  {
    $sql = "SELECT * FROM audit WHERE resource = :resource AND event IN (:events)";
    $stmt = $this->dbadmin()->prepare($sql);
    $stmt->bindParam(':resource', $resource, PDO::PARAM_STR);
    $stmt->bindParam(':events', $events, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetchAll();
    return $result;
  }


  /**
   * Get All Logs By Resource and Events.
   *
   * @param string $resource
   * @param array $events
   *
   * @return int
   */
  public function countLogsByResourceAndEvents($resource, $events)
  {
  }

  /**
   * Delete all logs older than $timestamp seconds
   *
   * @param int $created_at
   *
   * @return bool
   */
  public function cleanup($created_at)
  {
    $sql = "DELETE FROM audit WHERE created_at < :created_at";
    $stmt = $this->dbadmin()->prepare($sql);
    $stmt->bindParam(':created_at', $created_at, PDO::PARAM_INT);
    $stmt->execute();
    return true;
  }
}
