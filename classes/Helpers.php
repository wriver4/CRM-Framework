<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

class Helpers extends Database
{

  public function __construct()
  {
    parent::__construct();
  }

  public function unset_page_variables()
  {
  unset($page);
  unset($dir);
  unset($subdir);
  //$table_page = true;
  //$table_header = true;
  /*
  $search = true;
  $button_showall = false;
  $button_new = true;
  $button_refresh = false;
  $button_back = false;
  $paginate = true; */
  
  }

  // password

  public function hash_password($password)
  {
    $hash = password_hash($password, PASSWORD_BCRYPT, array('cost' => 12));
    return $hash;
  }

  public function is_password($password)
  {
    $get_prefix = substr($password, 0, 3);
    $prefix = ['$2y', '$2a', '$2b', '$2x'];
    if (in_array($get_prefix, $prefix)) {
      return true;
    } else {
      return false;
    }
  }

  public function verify_password($password, $hash)
  {
    $get_prefix = substr($hash, 0, 3);
    switch ($get_prefix) {
      case '$2a':
        $converted = str_replace("$2a", "$2y", $hash);
        break;
      case '$2b':
        $converted = str_replace("$2b", "$2y", $hash);
        break;
      case '$2x':
        $converted = str_replace("$2x", "$2y", $hash);
        break;
      case '$2y':
        $converted = $hash;
        break;
      default:
        $converted = $hash;
        break;
    }
    $verify = password_verify($password, $converted);
    return $verify;
  }

  // Status 

  public function get_status($status)
  {
    $status = $status == 1 ? 'Active' : 'Inactive';
    return $status;
  }

  public function set_status($status)
  {
    echo '<option value="' . $status . '">' . $this->get_status($status) . '</option>';
    echo $status == 1 ? '<option value="0">Inactive</option>' : '<option value="1">Active</option>';
  }

  // Roles
  public function get_role_array($lang)
  {
    $role_array = [
      '21' => $lang['role_id_21'],
      '20' => $lang['role_id_20'],
      '19' => $lang['role_id_19'],
      '18' => $lang['role_id_18'],
      '17' => $lang['role_id_17'],
      '16' => $lang['role_id_16'],
      '15' => $lang['role_id_15'],
      '14' => $lang['role_id_14'],
      '13' => $lang['role_id_13'],
      '12' => $lang['role_id_12'],
      '11' => $lang['role_id_11'],
      '10' => $lang['role_id_10'],
      '9' => $lang['role_id_9'],
      '8' => $lang['role_id_8'],
      '7' => $lang['role_id_7'],
      '6' => $lang['role_id_6'],
      '5' => $lang['role_id_5'],
      '4' => $lang['role_id_4'],
      '3' => $lang['role_id_3'],
      '2' => $lang['role_id_2'],
      '1' => $lang['role_id_1'],
      '22' => $lang['role_id_22'],
    ];
    return $role_array;
  }

  public function select_role($lang, $rid = null)
  {
    if ($rid == null) {
      echo '<option value="">' . $lang['select_rid'] . '</option>';
    }
    $roles = $this->get_role_array($lang);
    foreach ($roles as $key => $value) {
      if ($key != 22) {
        echo '<option value="'
          . $key
          . '"'
          . ($rid == $key ? ' selected="selected">' : '>')
          . $value
          . '</option>';
      }
    }
  }

  // system state
  public function get_system_state_array($lang)
  {
    $system_state_array = [
      '1' => $lang['state_id_1'],
      '2' => $lang['state_id_2'],
      '3' => $lang['state_id_3'],
      '4' => $lang['state_id_4'],
      '5' => $lang['state_id_5'],
      '6' => $lang['state_id_6'],
      '7' => $lang['state_id_7'],
      '8' => $lang['state_id_8'],
      '9' => $lang['state_id_9'],
    ];
    return $system_state_array;
  }

  public function select_system_state($lang, $state_id = null)
  {
    $system_state = $this->get_system_state_array($lang);
    foreach ($system_state as $key => $value) {
      echo '<option value="'
        . $key
        . '"'
        . ($state_id == $key ? ' selected="selected">' : '>')
        . $value
        . '</option>';
    }
  }
  
  // Contact Type
  public function get_contact_type_array($lang)
  {
    $contact_array = [
      '1' => $lang['contact_id_1'], // Primary Owner
      '2' => $lang['contact_id_2'], // Secondary Owner
      '3' => $lang['contact_id_3'], // Additional Owner
      '4' => $lang['contact_id_4'], // Family On Site
      '5' => $lang['contact_id_5'], // Family Off Site
      '6' => $lang['contact_id_6'], // Owner's Rep.
      '7' => $lang['contact_id_7'], // Property Manager
      '8' => $lang['contact_id_8'], // Backup with POA
      '9' => $lang['contact_id_9'],  // Backup
      '10' => $lang['contact_id_10'], // Installer
      '11' => $lang['contact_id_11'], // Developer
      '12' => $lang['contact_id_12'], // Builder
      '13' => $lang['contact_id_13'], // IT Company
    ];
    return $contact_array;
  }

  public function get_contact_type($lang, $ctype)
  {
    $contact_type = $this->get_contact_type_array($lang);
    foreach ($contact_type as $key => $value) {
      if ($key == $ctype) {
        echo $value;
      }
    }
  }

  public function select_contact_type($lang, $contact_id = null)
  {
    $contact_type = $this->get_contact_type_array($lang);
    if ($contact_id == null) {
      echo '<option value="" >Select Contact Type</option>';
    }
    foreach ($contact_type as $key => $value) {
      echo '<option value="'
        . $key
        . '"'
        . ($contact_id == $key ? ' selected="selected">' : '>')
        . $value
        . '</option>';
    }
  }

  //properties
  public function clean_prop_id($prop_id)
  {
    $prop_id = trim($prop_id);
    $prop_id = ltrim($prop_id, ',');
    $prop_id = rtrim($prop_id, ',');
    if (strpos($prop_id, ', ') == true){
      $prop_id = str_replace(", ", ",", $prop_id);
    }
    if (strpos($prop_id, ' ') == true){
      $prop_id = str_replace(" ", ",", $prop_id);
    }
    return $prop_id;
  }

  public function select_property_id($lang, $prop_id = null)
  {
    $properties = new Properties();
    if ($prop_id == null) {
      echo '<option value="">' . $lang['select_prop_id'] . '</option>';
    }
    foreach ($properties->get_id_prop_id_nickname_array() as $key => $value) {
      if ($value['prop_id'] > 300) {
        echo '<option value="'
          . $value['prop_id']
          . '"'
          . ($prop_id == $value['prop_id'] ? ' selected="selected">' : '>')
          . ''
          . $value['prop_id']
          . '&emsp;&emsp;'
          . $value['nickname']
          . '</option>';
      }
    }
  }

public function admin_select_property_id($lang, $prop_id )
  {
    $properties = new Properties();
    if ($prop_id == null) {
      echo '<option value="">' . $lang['select_prop_id'] . '</option>';
    }
    foreach ($properties->get_id_prop_id_nickname_array() as $key => $value) {
      //if ($value['prop_id'] > 300) {
        echo '<option value="'
          . $value['prop_id']
          . '"'
          . ($prop_id == $value['prop_id'] ? ' selected="selected">' : '>')
          . ''
          . $value['prop_id']
          . '&emsp;&emsp;'
          . $value['nickname']
          . '</option>';
      // }
    }
  }

  public function find_property_id($lang, $prop_id = null)
  {
    $properties = new Properties();
    if ($prop_id == null) {
      echo '<option value="">' . $lang['lookup_prop_id'] . '</option>';
    }
    foreach ($properties->get_id_prop_id_nickname_array() as $key => $value) {
      echo '<option value="'
        . $value['prop_id']
        . '"'
        . ($prop_id == $value['prop_id'] ? ' selected="selected">' : '>')
        . ''
        . $value['prop_id']
        . '&emsp;&emsp;'
        . $value['nickname']
        . '</option>';
    }
  }

  // states
  public function get_us_states_array($lang)
  {
    $us_states = [
      'US-AZ' => $lang['US-AZ'],
      'US-CA' => $lang['US-CA'],
      'US-CO' => $lang['US-CO'],
      'US-ID' => $lang['US-ID'],
      'US-MT' => $lang['US-MT'],
      'US-NV' => $lang['US-NV'],
      'US-NM' => $lang['US-NM'],
      'US-OR' => $lang['US-OR'],
      'US-TX' => $lang['US-TX'],
      'US-UT' => $lang['US-UT'],
      'US-WA' => $lang['US-WA'],
      'US-WY' => $lang['US-WY'],
      'US-VA' => $lang['US-VA'],
      'US-SC' => $lang['US-SC'],
    ];
    return $us_states;
  }

  public function select_us_state($lang, $state = null)
  {
    $us_states = $this->get_us_states_array($lang);
    if ($state == null) {
      echo '<option value="">' . $lang['select_state'] . '</option>';
    }
    foreach ($us_states as $key => $value) {
      echo '<option value="'
        . $key
        . '"'
        . ($state == $key ? ' selected="selected">' : '>')
        . $value
        . '</option>';
    }
  }

  //countries
  public function get_countries_array($lang)
  {
    $countries = [
      'US' => $lang['US'],
      'CA' => $lang['CA'],
      'MX' => $lang['MX'],
      'UK' => $lang['UK'],
      'AU' => $lang['AU'],
      'NZ' => $lang['NZ'],
      'BR' => $lang['BR'],
    ];
    return $countries;
  }

  public function select_country($lang, $country = null)
  {
    $countries = $this->get_countries_array($lang);
    if ($country == null) {
      echo '<option value="">' . $lang['select_country'] . '</option>';
    }
    foreach ($countries as $key => $value) {
      echo '<option value="'
        . $key
        . '"'
        . ($country == $key ? ' selected="selected">' : '>')
        . $value
        . '</option>';
    }
  }

  // status
  public function modes($lang, $value)
  {
    echo match ($value) {
      1 => '<td style="color:green;">' . $lang['mode_1'] . '</td>',
      2 => '<td style="color:red;"><b>' . $lang['mode_2'] . '</b></td>',
      3 => '<td style="color:orange;">' . $lang['mode_3'] . '</td>',
      4 => '<td style="color:red;"><i>' . $lang['mode_4'] . '</i></td>',
      5 => '<td style="color:red;"><i>' . $lang['mode_5'] . '</i></td>',
      6 => '<td style="color:red;"><i>' . $lang['mode_6'] . '</i></td>',
      7 => '<td style="color:red;"><i>' . $lang['mode_7'] . '</i></td>',
      8 => '<td style="color:aqua;">' . $lang['mode_8'] . '</td>',
      9 => '<td style="color:blue;"><i>' . $lang['mode_9'] . '</i></td>',
      10 => '<td style="color:black;">' . $lang['mode_10'] . '</td>',
      11 => '<td style="color:blue;">' . $lang['mode_11'] . '</td>',
      12 => '<td style="color:black;">' . $lang['mode_12'] . '</td>',
      66 => '<td>' . $lang['mode_66'] . '</td>',
      86 => '<td>' . $lang['mode_86'] . '</td>',
      99 => '<td>' . $lang['mode_99'] . '</td>',
      default => '<td> - </td>', // $value = NULL
    };
  }

  public function faults($lang, $value)
  {
    echo match ($value) {
      99 => '<td class="text-danger"><i>' . $lang['fault_99'] . '</i></td>',
      1 => '<td class="text-danger"><i>' . $lang['fault_1'] . '</i></td>',
      2 => '<td class="text-danger"><i>' . $lang['fault_2'] . '</i></td>',
      3 => '<td class="text-danger"><i>' . $lang['fault_3'] . '</i></td>',
      4 => '<td class="text-danger"><i>' . $lang['fault_4'] . '</i></td>',
      5 => '<td class="text-danger"><i>' . $lang['fault_5'] . '</i></td>',
      6 => '<td class="text-danger"><i>' . $lang['fault_6'] . '</i></td>',
      7 => '<td class="text-danger"><i>' . $lang['fault_7'] . '</i></td>',
      8 => '<td class="text-danger"><i>' . $lang['fault_8'] . '</i></td>',
      9 => '<td class="text-danger"><i>' . $lang['fault_9'] . '</i></td>',
      10 => '<td class="text-danger"><i>' . $lang['fault_10'] . '</i></td>',
      11 => '<td class="text-danger"><i>' . $lang['fault_11'] . '</i></td>',
      12 => '<td class="text-danger"><i>' . $lang['fault_12'] . '</i></td>',
      13 => '<td class="text-danger"><i>' . $lang['fault_13'] . '</i></td>',
      14 => '<td class="text-danger"><i>' . $lang['fault_14'] . '</i></td>',
      15 => '<td class="text-danger"><i>' . $lang['fault_15'] . '</i></td>',
      86 => '<td class="text-danger"><i>' . $lang['fault_86'] . '</i></td>',
      87 => '<td class="text-danger"><i>' . $lang['fault_87'] . '</i></td>',
      88 => '<td class="text-danger"><i>' . $lang['fault_88'] . '</i></td>',
      default => '<td></td>', // $value = NULL
    };
  }

  public function power($value)
  {
    echo match ($value) {
      1 => '<td style="color:green;text-align:center;"><i class="fa-solid fa-plug"></i></td>',
      2 => '<td style="color:red;"><i class="fa-solid fa-car-battery"></i></td>',
      default => '<td><i style="color:green;" class="fa-solid fa-plug"></i></td>', // $value = NULL
    };
  }

  public function tanks($value)
  {
    echo match ($value) {
      0 => '<td>No Tank</td>',
      1 => '<td><i style="color:red"; class="fa-solid fa-water"></i></td>',
      2 => '<td style="color:yellow;"><i class="fa-solid fa-water"></i>_<i class="fa-solid fa-water"></i></td>',
      3 => '<td style="color:blue;"><i class="fa-solid fa-water"></i>_<i class="fa-solid fa-water"></i>_<i class="fa-solid fa-water"></i></td>',
      4 => '<td style="color:blue;"><i class="fa-solid fa-water">_</i><i class="fa-solid fa-water"></i>_<i class="fa-solid fa-water"></i>_<i class="fa-solid fa-water"></i>_<i class="fa-solid fa-water"></i></td>',
      default => '<td> - </td>', // $value = NULL
    };
  }

  // prefered lang
  public function get_current_languages($pdo)
  {
    $sql = "SELECT * FROM languages ORDER BY plang_id ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $langs = $stmt->fetchAll();
    foreach ($langs as $plang) {
      echo '<option value="' . $langs['plang_id'] . '">' . $langs['plang'] . '</option>';
    }
  }

  // MISC
  public function ago($time)
  {
    $periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
    $lengths = array("60", "60", "24", "7", "4.35", "12", "10");
    $now = time();
    $difference = $now - $time;
    $tense = 'ago';
    for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths) - 1; $j++) {
      $difference /= $lengths[$j];
    }
    $difference = round($difference);
    if ($difference != 1) {
      $periods[$j] .= "s";
    }
    return $difference . " " . $periods[$j] . " ago";
  }

  public function messageLoggedInUser()
  {
    // get logged in users
  }

  public function sessionmanager()
  {
    if (isset($_SESSION["user_id"])) {
      if (time() - $_SESSION["login_time_stamp"] > 600) {
        session_unset();
        session_destroy();
        header("Location:login.php");
      }
    } else {
      header("Location:login.php");
    }
  }

  // url conversion
  public function api_to_query_string($url, $show_schema = false, $show_host = false)
  {
    /** TODO: Add support for multiple query parameters and subdir */
    // schema and host are optional and for future use
    $new_url = '';
    $path = ['dir', 'page', 'id'];
    $parsed_url = parse_url($url);
    if ($show_schema) {
      $new_url .= $parsed_url['schema'] . '://';
    }
    if ($show_host) {
      $new_url .= $parsed_url['host'] . '/';
    }
    $items = array_filter(explode('/', $parsed_url['path']));
    $merged = array_combine($path, $items);
    $newurl = "/";
    foreach ($merged as $key => $value) {
      switch ($key) {
        case 'dir':
          $newurl .= $value . '/';
          break;
        case 'subdir':
          $newurl .= $value . '/';
          break;
        case 'page':
          $newurl .= $value;
          break;
        default:
          $newurl = $newurl . '?' . $key . '=' . $value;
          break;
      }
    }
    return $newurl;
  }

  public function query_string_to_api($url, $show_schema = false, $show_host = false)
  {
    /** TODO: Add support for multiple query parameters */
    // schema and host are optional and for future use
    $new_url = '';
    $parsed_url = parse_url($url);
    if ($show_schema) {
      $new_url .= $parsed_url['schema'] . '://';
    }
    if ($show_host) {
      $new_url .= $parsed_url['host'] . '/';
    }
    parse_str($parsed_url['query'], $query);
    $key = key($query);
    $new_url = "/" . $query[$key];
    return $new_url;
  }

  public function exceptions()
  {
    $exceptions = [
      E_ERROR => "E_ERROR", // 1
      E_WARNING => "E_WARNING", // 2
      E_PARSE => "E_PARSE", // 4
      E_NOTICE => "E_NOTICE", // 8
      E_CORE_ERROR => "E_CORE_ERROR", // 16
      E_CORE_WARNING => "E_CORE_WARNING", // 32
      E_COMPILE_ERROR => "E_COMPILE_ERROR", // 64
      E_COMPILE_WARNING => "E_COMPILE_WARNING", // 128
      E_USER_ERROR => "E_USER_ERROR", // 256
      E_USER_WARNING => "E_USER_WARNING", // 512
      E_USER_NOTICE => "E_USER_NOTICE", // 1024
      E_STRICT => "E_STRICT", // 2048
      E_RECOVERABLE_ERROR => "E_RECOVERABLE_ERROR", // 4096
      E_DEPRECATED => "E_DEPRECATED", // 8192
      E_USER_DEPRECATED => "E_USER_DEPRECATED", // 16384
      E_ALL => "E_ALL" // 32767
    ];
  }


}
