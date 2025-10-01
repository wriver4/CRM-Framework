<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/config/system.php';
$not->loggedin();
/*if ($winterized){
  $results = $status->show_winterized();
  echo '<table>';
  echo '<tr>';
  echo '<th>System ID</th>';
  echo '<th>Prop ID</th>';
  echo '</tr>';
  foreach ($results as $result) {
    echo '<tr>';
    foreach ($result as $key => $value) {
      switch ($key) {
        case 'system_id':
          echo "<td>$value<td>";
          break;
        case 'prop_id':
          echo "<td>$value<td>";
          break;
        default:
          echo "<td>$value<td>";
      }
    }
    echo '</tr>';
  }
  echo '</table>';
}*/
/*if ($removed){
  $results = $status->show_removed();
  echo '<table>';
  echo '<tr>';
  echo '<th>System ID</th>';
  echo '<th>Prop ID</th>';
  echo '</tr>';
  foreach ($results as $result) {
    echo '<tr>';
    foreach ($result as $key => $value) {
      switch ($key) {
        case 'system_id':
          echo "<td>$value<td>";
          break;
        case 'prop_id':
          echo "<td>$value<td>";
          break;
        default:
          echo "<td>$value<td>";
      }
    }
    echo '</tr>';
  }
  echo '</table>';
}*/
