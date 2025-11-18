<div class="footer pt-4">©
  <?php
  echo $copyYear = 2022;
  $curYear = date('Y');
  echo $copyYear != $curYear ? '-' . $curYear : '';
  echo "&nbsp;";
  // Ensure $lang is an array and has the required key
  if (!is_array($lang)) {
    $lang = ['copyright' => 'Copyright', 'all_rights_reserved' => 'All rights reserved'];
  }
  echo isset($lang['copyright']) ? $lang['copyright'] : 'Copyright';
  ?>&nbsp;waveGUARD&TRADE;&nbsp;Corporation.&nbsp;
  <?= isset($lang['all_rights_reserved']) ? $lang['all_rights_reserved'] : 'All rights reserved'; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa"
        crossorigin="anonymous"></script>
<?php
if ($page != 'login') {
  echo '<script src="' . JS . '/general.js"></script>';
}
// validation
if ($page == 'new' || $page == 'edit') {
  echo '<script src="/assets/js/validator.min.js"></script>';
}

// conditional forms
if ($dir == 'leads' && $page == 'new') {
  echo '<script src="/assets/js/conditional-forms.js"></script>';
}
// hide empty Structure Other/Additional on edit view
if ($dir == 'leads' && $page == 'edit') {
  // Load jQuery first for leads edit functionality
  echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.1/jquery.min.js" integrity="sha512-aVKKRRi/Q/YV+4mjoKBsE4x3H+BkegoM/em46NNlCqNTmUYADjBbeNefNxYV7giUp0VxICtqdrbqU7iVaeZNXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>';
  echo '<script src="' . JS . '/hide-empty-structure.js"></script>';
  echo '<script src="/assets/js/contact-selector.js"></script>';
  
  // Data injection for leads edit
  echo '<script>';
  echo 'window.leadsEditData = {';
  echo '  leadId: ' . ($internal_id ?? 0) . ',';
  echo '  selectedStage: ' . ($selected_stage ?? 1) . ',';
  echo '  leadIdText: \'' . htmlspecialchars($lead_id ?? '') . '\',';
  echo '  clientState: \'' . htmlspecialchars($form_state ?? '') . '\',';
  echo '  clientCountry: \'' . htmlspecialchars($form_country ?? '') . '\',';
  echo '  stageNames: ' . json_encode($leads->get_lead_stage_array()) . ',';
  echo '  usTimezones: ' . json_encode($helpers->get_us_timezone_array()) . ',';
  echo '  countryTimezones: ' . json_encode($helpers->get_country_timezone_array()) . ',';
  echo '  errorUnableDetectTimezone: \'' . ($lang['error_unable_detect_timezone'] ?? 'Unable to detect timezone') . '\',';
  echo '  textUnknown: \'' . ($lang['text_unknown'] ?? 'Unknown') . '\',';
  echo '  errorUnableConvertTime: \'' . ($lang['error_unable_convert_time'] ?? 'Unable to convert time') . '\',';
  echo '  errorFailedLoadNotes: \'' . ($lang['error_failed_load_notes'] ?? 'Failed to load notes') . '\',';
  echo '  errorUnknownError: \'' . ($lang['error_unknown_error'] ?? 'Unknown error') . '\',';
  echo '  errorNetworkLoadingNotes: \'' . ($lang['error_network_loading_notes'] ?? 'Network error while loading notes') . '\',';
  echo '  textFrom: \'' . ($lang['text_from'] ?? 'from') . '\',';
  echo '  textSameTimezone: \'' . ($lang['text_same_timezone'] ?? 'Same timezone') . '\',';
  echo '  textTimeConversion: \'' . ($lang['text_time_conversion'] ?? '{clientTime} ({clientTz}) = {userTime} ({userTz})') . '\',';
  echo '  errorTimeConversion: \'' . ($lang['error_time_conversion'] ?? 'Time conversion error') . '\'';
  echo '};';
  echo '</script>';
  
  echo '<script src="/assets/js/edit-leads.js"></script>';
}
// dev only
/*
// Defining a function to display error message
function printError(elemId, hintMsg) {
    document.getElementById(elemId).innerHTML = hintMsg;
}

// Defining a function to validate form 
function validateForm() {
    // Retrieving the values of form elements 
    let name = document.contactForm.name.value;
    let email = document.contactForm.email.value;
    let mobile = document.contactForm.mobile.value;
    let ipaddress = document.contactForm.ipaddress.value;
    let country = document.contactForm.country.value;
    let gender = document.contactForm.gender.value;
    let hobbies = [];
    let checkboxes = document.getElementsByName("hobbies[]");
    for(let i=0; i < checkboxes.length; i++) {
        if(checkboxes[i].checked) {
            // Populate hobbies array with selected values
            hobbies.push(checkboxes[i].value);
        }
    }
    
	// Defining error variables with a default value
    let nameErr = emailErr = mobileErr = countryErr = genderErr = true;
    
    // Validate name
    if(name == "") {
        printError("nameErr", "Please enter your name");
    } else {
        let regex = /^[a-zA-Z\s]+$/;                
        if(regex.test(name) === false) {
            printError("nameErr", "Please enter a valid name");
        } else {
            printError("nameErr", "");
            nameErr = false;
        }
    }
    
    // Validate email address
    if(email == "") {
        printError("emailErr", "Please enter your email address");
    } else {
        // Regular expression for basic email validation
        let regex = /^\S+@\S+\.\S+$/;
        if(regex.test(email) === false) {
            printError("emailErr", "Please enter a valid email address");
        } else{
            printError("emailErr", "");
            emailErr = false;
        }
    }
    
    // Validate ip address
    if(ipaddress == "") {
        printError("ipaddressErr", "Please enter your ip address");
    } else {
        let regex = /^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$/;
        if(regex.test(ipaddress) === false) {
            printError("ipaddressErr", "Please enter a valid ip address");
        } else{
            printError("ipaddressErr", "");
            ipaddressErr = false;
        }
    }  
    // Validate mobile number
    if(mobile == "") {
        printError("mobileErr", "Please enter your mobile number");
    } else {
        let regex = /^[1-9]\d{9}$/;
        if(regex.test(mobile) === false) {
            printError("mobileErr", "Please enter a valid 10 digit mobile number");
        } else{
            printError("mobileErr", "");
            mobileErr = false;
        }
    }
    
    // Validate country
    if(country == "Select") {
        printError("countryErr", "Please select your country");
    } else {
        printError("countryErr", "");
        countryErr = false;
    }
    
    // Validate gender
    if(gender == "") {
        printError("genderErr", "Please select your gender");
    } else {
        printError("genderErr", "");
        genderErr = false;
    }
    
    // Prevent the form from being submitted if there are any errors
    if((nameErr || emailErr || mobileErr || countryErr || genderErr) == true) {
       return false;
    } else {
        // Creating a string from input data for preview
       let dataPreview = "You have entered the following details: " + "\n" +
                          "Full Name: " + name + "\n" +
                          "Email Address: " + email + "\n" +
                          "Mobile Number: " + mobile + "\n" +
                          "Country: " + country + "\n" +
                          "Gender: " + gender + "\n";
        if(hobbies.length) {
            dataPreview += "Hobbies: " + hobbies.join(", ");
        }
        // Display input data in a dialog box before submitting the form
        alert(dataPreview);
    }
};
</script>';
/* end dev only */



if ($dir == 'test') {
  echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.1/jquery.min.js" integrity="sha512-aVKKRRi/Q/YV+4mjoKBsE4x3H+BkegoM/em46NNlCqNTmUYADjBbeNefNxYV7giUp0VxICtqdrbqU7iVaeZNXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>';
}
if ($dir == 'tickets' && $page == 'view') {
  echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.1/jquery.min.js" integrity="sha512-aVKKRRi/Q/YV+4mjoKBsE4x3H+BkegoM/em46NNlCqNTmUYADjBbeNefNxYV7giUp0VxICtqdrbqU7iVaeZNXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>';
  echo '<script src="' . JS . '/tickets_view_labor.js"></script>';
}

// Calendar module
if ($dir == 'calendar') {
  echo '<script src="' . ASSETS . '/vendor/fullcalendar/index.global.js"></script>';
  echo '<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/bootstrap5@6.1.19/index.global.min.js"></script>';
  echo '<script src="' . JS . '/calendar.js"></script>';
}



// datatables
if ($table_page == true) {
  echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.1/jquery.min.js" integrity="sha512-aVKKRRi/Q/YV+4mjoKBsE4x3H+BkegoM/em46NNlCqNTmUYADjBbeNefNxYV7giUp0VxICtqdrbqU7iVaeZNXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>';
  echo '<script type="text/javascript" src="https://cdn.datatables.net/v/bs5/dt-1.12.1/fh-3.2.4/r-2.3.0/datatables.min.js"></script>';


  if ($page == 'list') {
    echo '<script>'
      .'$.fn.dataTable.ext.errMode = \'none\';'
      .' $(document).ready(function() {'
      .'  $("table").DataTable({'
      .'   "lengthChange": false,';
  if ($dir == 'tickets') {
    echo '   "pageLength": 7,'
        .'    order: [[7, "asc"]],'
        .'    columnDefs: ['
        .'     { orderable: false, targets: [0,1,3,4,5,6,7,8,10] }'
        .'    ],';
  }
  if ($dir == 'contacts') {
    echo '   "pageLength": 7,'
        .'    order: [[1, "asc"]],'
        .'    columnDefs: ['
        .'     { orderable: false, targets: [0] }'
        .'    ],';
  }
  if ($dir == 'systems') {
    echo '   "pageLength": 8,'
        .'    order: [[1, "asc"]],'
        .'    columnDefs: ['
        .'     { orderable: false, targets: [0] }'
        .'    ],';
  }
  if ($dir == 'users') {
    echo '   "pageLength": 8,'
        .'    order: [[1, "asc"]],'
        .'    columnDefs: ['
        .'     { orderable: false, targets: [0] }'
        .'    ],';
  }
  if ($dir == 'leads') {
    echo '   "pageLength": 7,'
        .'    order: [[1, "desc"]],'
        .'    columnDefs: ['
        .'     { orderable: false, targets: [0] }'
        .'    ],';
  }
  if ($dir == 'status' || $dir == 'testing') {
    echo '   "pageLength": 120,'
        .'    order: [[1, "asc"]],'
        .'    columnDefs: ['
        .'     { orderable: false, targets: _all }'
        .'    ],';
  }
  // Admin email system pages
  if ($dir == 'admin' && $subdir == 'email') {
    if ($page == 'processing_log' || $page == 'sync_queue') {
      echo '   "pageLength": 10,'
          .'    order: [[0, "desc"]],'
          .'    columnDefs: ['
          .'     { orderable: false, targets: [0] }'
          .'    ],';
    } elseif ($page == 'accounts_config') {
      echo '   "pageLength": 10,'
          .'    order: [[1, "asc"]],'
          .'    columnDefs: ['
          .'     { orderable: false, targets: [0] }'
          .'    ],';
    }
  }
  // Admin security pages
  if ($dir == 'admin' && $subdir == 'security') {
    echo '   "pageLength": 10,'
        .'    order: [[1, "asc"]],'
        .'    columnDefs: ['
        .'     { orderable: false, targets: [0] }'
        .'    ],';
  }
  echo '  });'
      .'});'
      . '</script>'; 
  }
  echo '<script>
  $("#search").keyup(function() {
      var table = $("table").DataTable();
      table.search($(this).val()).draw();
  });
  </script>';

}
  if ($dir == 'tickets' && $page == 'view') {

    echo  '<script> $(document).ready(function() {
    $(\'input[rel="txtTooltip"]\').tooltip();
    });</script>';
  }

// Summernote WYSIWYG Editor Integration
try {
  $editorHelper = EditorHelper::getInstance();
  
  // Check if current page should load Summernote
  if ($editorHelper->shouldLoadEditor($dir ?? '', $page ?? '')) {
    // Configure editor for current page
    $editorHelper->configureForPage($dir ?? '', $page ?? '');
    
    // Output JavaScript includes and initialization
    echo $editorHelper->getJsIncludes();
    echo $editorHelper->getInitializationScript();
    
    // Load helper JavaScript for additional functionality
    echo '<script src="' . JS . '/summernote-helper.js"></script>';
  }
} catch (Error $e) {
  // Silently fail if EditorHelper is not available
  error_log("EditorHelper not available: " . $e->getMessage());
}

echo '</body>

</html>';