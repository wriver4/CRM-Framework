const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

function password_show_hide () {
	var x = document.getElementById("password");
	var show_eye = document.getElementById("show_eye");
	var hide_eye = document.getElementById("hide_eye");
	hide_eye.classList.remove("d-none");
	if (x.type === "password") {
		x.type = "text";
		show_eye.style.display = "none";
		hide_eye.style.display = "block";
	} else {
		x.type = "password";
		show_eye.style.display = "block";
		hide_eye.style.display = "none";
	}
}

/*$(document).ready(function () {
	$("#show_hide_password a").on('click', function (event) {
		event.preventDefault();
		if ($('#show_hide_password input').attr("type") == "text") {
			$('#show_hide_password input').attr('type', 'password');
			$('#show_hide_password i').addClass("fa-eye-slash");
			$('#show_hide_password i').removeClass("fa-eye");
		} else if ($('#show_hide_password input').attr("type") == "password") {
			$('#show_hide_password input').attr('type', 'text');
			$('#show_hide_password i').removeClass("fa-eye-slash");
			$('#show_hide_password i').addClass("fa-eye");
		}
	});
});
*/
/*
$(document).ready(function () {


		var userData = $('#listUser').DataTable({
			"searching": false,
			"lengthChange": false,
			"processing": true,
			"serverSide": true,
			"order": [],
			"ajax": {
				url: "user_action.php",
				type: "POST",
				data: { action: 'listUser' },
				dataType: "json"
			},
			"columnDefs": [
				{
					"targets": [0, 6, 7],
					"orderable": false,
				},
			],
			//"pageLength": 10
		});
	

	

	
	
		$(document).on('click', '.update', function () {
			var userId = $(this).attr("id");
			var action = 'getUserDetails';
			$.ajax({
				url: 'user_action.php',
				method: "POST",
				data: { userId: userId, action: action },
				dataType: "json",
				success: function (data) {
					$('#userModal').modal('show');
					$('#userId').val(data.id);
					$('#userName').val(data.name);
					$('#email').val(data.email);
					$('#role').val(data.user_type);
					$('#status').val(data.status);
					$('.modal-title').html("<i class='fa fa-plus'></i> Edit User");
					$('#action').val('updateUser');
					$('#save').val('Save');
				}
			});
		});

	$('#addUser').click(function () {
		$('#userModal').modal('show');
		$('#userForm')[0].reset();
		$('.modal-title').html("<i class='fa fa-plus'></i> Add User");
		$('#action').val('addUser');
		$('#save').val('Save');
	});

	$(document).on('submit', '#userForm', function (event) {
		event.preventDefault();
		$('#save').attr('disabled', 'disabled');
		var formData = $(this).serialize();
		$.ajax({
			url: "user_action.php",
			method: "POST",
			data: formData,
			success: function (data) {
				$('#userForm')[0].reset();
				$('#userModal').modal('hide');
				$('#save').attr('disabled', false);
				userData.ajax.reload();
			}
		});
	});

	$(document).on('click', '.delete', function () {
		var userId = $(this).attr("id");
		var action = "deleteUser";
		if (confirm("Are you sure you want to delete this record?")) {
			$.ajax({
				url: "user_action.php",
				method: "POST",
				data: { userId: userId, action: action },
				success: function (data) {
					userData.ajax.reload();
				}
			});
		} else {
			return false;
		}
	});

});

*/
