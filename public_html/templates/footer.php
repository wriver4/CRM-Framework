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
  echo '<script src="' . JS . '/hide-empty-structure.js"></script>';
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
        .'    "ordering": false,'
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

if ($dir == 'systems' && $page == 'edit') {
echo '<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
    ClassicEditor
        .create( document.querySelector( \'#floatingTextarea\' ), {
            toolbar: [\'undo\', \'redo\', \'|\', \'heading\', \'|\', \'bold\', \'italic\', \'|\',  \'bulletedList\', \'numberedList\', \'blockQuote\'],
            heading: {
                options: [
                    { model: \'paragraph\', title: \'Paragraph\', class: \'ck-heading_paragraph\' },
                    { model: \'heading1\', view: \'h1\', title: \'Heading 1\', class: \'ck-heading_heading1\' },
                    { model: \'heading2\', view: \'h2\', title: \'Heading 2\', class: \'ck-heading_heading2\' },
                    { model: \'heading3\', view: \'h3\', title: \'Heading 3\', class: \'ck-heading_heading3\' }
                ]
            }
        } )
        .catch( error => {
            console.error( error );
        } );
</script>';
}

echo '</body>

</html>';