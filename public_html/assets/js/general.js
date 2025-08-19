/*$(document).ready(function(){
	var urlPath = window.location.pathname,
		urlPathArray = urlPath.split('.'),
		tabId = urlPathArray[0].split('/').pop();
	$('#department, #user, #ticket').removeClass('active');	
	$('#'+tabId).addClass('active');
});*/

let dropdowns = document.querySelectorAll('.dropdown-toggle')
dropdowns.forEach((dd) => {
	dd.addEventListener('click', function (e) {
		var el = this.nextElementSibling
		el.style.display = el.style.display === 'block' ? 'none' : 'block'
	})
});

/*
(function ($bs) {
	const CLASS_NAME = 'has-child-dropdown-show';
	$bs.Dropdown.prototype.toggle = function (_orginal) {
		return function () {
			document.querySelectorAll('.' + CLASS_NAME).forEach(function (e) {
				e.classList.remove(CLASS_NAME);
			});
			let dd = this._element.closest('.dropdown').parentNode.closest('.dropdown');
			for (; dd && dd !== document; dd = dd.parentNode.closest('.dropdown')) {
				dd.classList.add(CLASS_NAME);
			}
			return _orginal.call(this);
		}
	}($bs.Dropdown.prototype.toggle);

	document.querySelectorAll('.dropdown').forEach(function (dd) {
		dd.addEventListener('hide.bs.dropdown', function (e) {
			if (this.classList.contains(CLASS_NAME)) {
				this.classList.remove(CLASS_NAME);
				e.preventDefault();
			}
			e.stopPropagation(); // do not need pop in multi level mode
		});
	});

	// for hover
	document.querySelectorAll('.dropdown-hover, .dropdown-hover-all .dropdown').forEach(function (dd) {
		dd.addEventListener('mouseenter', function (e) {
			let toggle = e.target.querySelector(':scope>[data-bs-toggle="dropdown"]');
			if (!toggle.classList.contains('show')) {
				$bs.Dropdown.getOrCreateInstance(toggle).toggle();
				dd.classList.add(CLASS_NAME);
				$bs.Dropdown.clearMenus();
			}
		});
		dd.addEventListener('mouseleave', function (e) {
			let toggle = e.target.querySelector(':scope>[data-bs-toggle="dropdown"]');
			if (toggle.classList.contains('show')) {
				$bs.Dropdown.getOrCreateInstance(toggle).toggle();
			}
		});
	});
})(bootstrap);
*/
