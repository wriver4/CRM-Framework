<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

class Nonce
{
  /**
   * Notes:
   * $bytes = random_bytes(14);
   * var_dump(bin2hex($bytes));
   * 
   * Usuage/Testing:
   * $nonce = new Nonce();
   * $myToken = $nonce->create('form_login');
   * var_dump($myToken);
   * $result = $nonce->verify($myToken);
   * var_dump($result);
   * Todo:
   * Check libsodium for better random bytes even though included in PHP
   * Double check the false conditions 
   */

  protected $age = 1000;
  protected $secret; // Declare property to avoid PHP 8.4 deprecation

  public function __construct($age = null)
  {
    if ($age) {
      $this->age = $age;
    }

    $this->secret = NONCE_SECRET;
  }

  private function store($form_id, $nonce)
  {
    if (is_string($form_id) == false) {
      throw new InvalidArgumentException("A valid Form ID is required");
    }
    $_SESSION['nonce'][$form_id] = md5($nonce);
    return true;
  }

  public function create($form_id)
  {
    if (is_string($this->secret) == false || strlen($this->secret) < 14) {
      throw new InvalidArgumentException("A valid Nonce Secret is required");
    }

    $salt = random_bytes(14);
    $saltEncoded = base64_encode($salt); // Encode binary salt to prevent colon issues
    $time = time() + $this->age;
    $toHash = $this->secret . $salt . $time;
    $nonce = $saltEncoded . ':' . $form_id . ':' . $time . ':' . hash('sha256', $toHash);
    $this->store($form_id, $nonce);
    return $nonce;
  }
  public function verify($nonce)
  {
    $split = explode(':', $nonce);
    if (count($split) !== 4) {
      return false;
    }
    $saltEncoded = $split[0];
    $salt = base64_decode($saltEncoded); // Decode the base64-encoded salt
    $form_id = $split[1];
    $time = intval($split[2]);
    $oldHash = $split[3];
    if (time() > $time) {
      return false;
    }
    if (isset($_SESSION['nonce'][$form_id])) {
      if ($_SESSION['nonce'][$form_id] !== md5($nonce)) {
        return false;
      }
    } else {
      return false;
    }
    $toHash = $this->secret . $salt . $time;
    $reHashed = hash('sha256', $toHash);
    if ($reHashed !== $oldHash) {
      return false;
    }
    return true;
  }
}
