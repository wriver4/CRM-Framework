<?php

// Build simple URL based on page variables
$list_url = '/' . $dir;
if (!empty($subdir)) $list_url .= '/' . $subdir;
if (!empty($sub_subdir)) $list_url .= '/' . $sub_subdir;
if (!empty($sub_sub_subdir)) $list_url .= '/' . $sub_sub_subdir;
$list_url .= '/list';

$new_url = '/' . $dir;
if (!empty($subdir)) $new_url .= '/' . $subdir;
if (!empty($sub_subdir)) $new_url .= '/' . $sub_subdir;
if (!empty($sub_sub_subdir)) $new_url .= '/' . $sub_sub_subdir;
$new_url .= '/new';

if (isset($button_back) && $button_back == true) {
   echo '<a href="' . $list_url . '" class="btn btn-success" tabindex="0" role="button" aria-pressed="false">'
      . $lang['back']
      . '</a>&emsp;';
}
if (isset($button_refresh) && $button_refresh == true) {
   echo '<a href="' . $list_url . '" class="btn btn-info" tabindex="0" role="button" aria-pressed="false">'
      . $lang['refresh']
      . '</a>';
   // echo '&emsp;<a href="#" class="btn btn-danger" tabindex="0" role="button" aria-pressed="false"><i class="fa-solid fa-hand"></i>&ensp;' . $lang['norefresh'] . '</a>';
}
/*
if ($filter_state) {
   echo '<form method="POST" action="">
            <select class="btn form-select bg-secondary" aria-label="Filter by State">
            <option selected>Filter by State&emsp;</option>
            <option value="1">Manufacturing</option>
            <option value="2">Installation</option>
            <option value="3">Operation</option>
            <option value="4">Winterized</option>
            <option value="5">Development</option>
            <option value="6">Sales</option>
            </select>
         </form>';
   echo '</div>';

}
*/
if (isset($button_showall) && $button_showall == true) {
   echo '<a href="' . $list_url . '" class="btn btn-info mx-2" tabindex="0" role="button" aria-pressed="false"><i class="fa fa-list"></i>&ensp;'
      . $lang['showall']
      . '</a>';
}
if (isset($button_new) && $button_new == true) {
   echo '<a href="' . $new_url . '" class="btn btn-success mx-2" tabindex="0" role="button" aria-pressed="false">'
      . $new_icon
      . '&ensp;'
      . $new_button
      . '</a>';
}
