
/* Module-specific javascript can be placed here */

$(document).ready(function() {
	handleButton($('#et_save'),function() {
	});

    handleButton($('#et_print'),function(e) {
        e.preventDefault();
        printEvent(null);
    });
	
	handleButton($('#et_cancel'),function(e) {
		if (m = window.location.href.match(/\/update\/[0-9]+/)) {
			window.location.href = window.location.href.replace('/update/','/view/');
		} else {
			window.location.href = baseUrl+'/patient/episodes/'+et_patient_id;
		}
		e.preventDefault();
	});

	$('tr.clickable').click(function(e) {
		e.preventDefault();
		window.location.href = $(this).data('uri');
	});
	
	handleButton($('#et_deleteevent'));

	handleButton($('#et_canceldelete'),function(e) {
		if (m = window.location.href.match(/\/delete\/[0-9]+/)) {
			window.location.href = window.location.href.replace('/delete/','/view/');
		} else {
			window.location.href = baseUrl+'/patient/episodes/'+et_patient_id;
		}
		e.preventDefault();
	});

	$('#search_dna_sample').click(function(e) {
		e.preventDefault();
		window.location.href = baseUrl+'/OphInDnasample/search/dnaSample?date-from='+$('#date-from').val()+'&date-to='+$('#date-to').val()+'&sample-type='+$('#sample-type').val()+'&comment='+$('#comment').val()+'&disorder-id='+$('#savedDiagnosis').val() + '&first_name=' + $('#first_name').val() + '&last_name=' + $('#last_name').val() + '&hos_num=' + $('#hos_num').val() + '&search=search';
	});

	$('select.populate_textarea').unbind('change').change(function() {
		if ($(this).val() != '') {
			var cLass = $(this).parent().parent().parent().attr('class').match(/Element.*/);
			var el = $('#'+cLass+'_'+$(this).attr('id'));
			var currentText = el.text();
			var newText = $(this).children('option:selected').text();

			if (currentText.length == 0) {
				el.text(ucfirst(newText));
			} else {
				el.text(currentText+', '+newText);
			}
		}
	});

	(function addNewTest() {

		var html = $('#add-new-test-template').html();

		var dialog = new OpenEyes.UI.Dialog({
			destroyOnClose: false,
			title: 'Add a new test',
			content: html,
			dialogClass: 'dialog event add-event',
			width: 580,
			id: 'add-new-test-dialog'
		});

		$('#et_add_test').click(function() {
			dialog.open();
		});
	}());

	$('#Element_OphInDnasample_Sample_type_id').on('change',function(){
		if( $(this).val() == 4 ){ //as 'other'
			$('#div_Element_OphInDnasample_Sample_other_sample_type').slideDown();
		}
		else{
			$('#div_Element_OphInDnasample_Sample_other_sample_type').slideUp();
			$('#Element_OphInDnasample_Sample_other_sample_type').val('');
		}
	});

});

function ucfirst(str) { str += ''; var f = str.charAt(0).toUpperCase(); return f + str.substr(1); }

function eDparameterListener(_drawing) {
	if (_drawing.selectedDoodle != null) {
		// handle event
	}
}