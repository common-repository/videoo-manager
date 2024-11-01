(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */



	 $("#ads-txt-lines").ready(function(){
		 let lines_data = {
			 'action': 'show_ads_txt_lines',
			 'data': '0'
		 }
		 presentAdsTxtLines(lines_data);

		 $("#save-ads-txt-settings").on("click", function(e){
			 e.preventDefault();

			 saveNewLines($("#videoo_manager_ads_txt_new_lines"));
			 toggleActive($("#videoo_manager_ads_txt_active_yes, #videoo_manager_ads_txt_active_no")); // check if has ::before
		 });

	 });

	 /**
	 * Elimina el color seleccionado
	 */
	 function presentAdsTxtLines(data) {
		 $.post(ajaxurl, data, function(response) {
			 let data_r = JSON.parse(response);
			 addNewLines(data_r);
		 });
	 }

	 function addNewLines(new_lines) {
		 new_lines.forEach((line, i) => {
			if ("" != line) {
				$("#ads-txt-lines").append(createLine(line, i));
				$(`#videoo_manager_ads_txt_line_${i}`).on('click focusin', function() {
				 $(this).removeAttr("readonly");
				});
				$(`#videoo_manager_ads_txt_line_${i}`).on('focusout', function() {
					$(this).attr("readonly", true);
				});
				$(`#delete_line_btn_${i}`).on('click', function(e){
					e.preventDefault();
					launchLineModal($(this).parent());
				});
				$(`#update_line_btn_${i}`).on('click', function(e){
					e.preventDefault();
					updateLine($(this).parent());
				});
			}
		 });
	 }

	 function createLine(line_data, line_index) {

		let lineWrapper = document.createElement("div");
		let deleteLineBtn = document.createElement("button");
		let updateLineBtn = document.createElement("button");
		let line_input = document.createElement('input');

		line_input.type = "text";
		line_input.classList.add("line-input");
		line_input.name = `videoo_manager_ads_txt_line_${line_index}`;
		line_input.id = `videoo_manager_ads_txt_line_${line_index}`;
		line_input.dataset.value = line_data;
		line_input.value = line_data;
		line_input.readOnly = true;

		lineWrapper.classList.add(`line_${line_index}`, "ads_txt_line");
		deleteLineBtn.innerText = "Delete";
		deleteLineBtn.classList.add("line-btn");
		deleteLineBtn.id = `delete_line_btn_${line_index}`;
		updateLineBtn.innerText = "Save";
		updateLineBtn.classList.add("line-btn");
		updateLineBtn.id = `update_line_btn_${line_index}`;

		lineWrapper.append(line_input, deleteLineBtn, updateLineBtn);

		return lineWrapper;
	 }

	 function deleteLine(line) {
		 let lines_data = {
			 'action': 'delete_ads_txt_lines',
			 'data': $(line).find(".line-input").val()
		 }
		 $.post(ajaxurl, lines_data, function(response) {
			 let data_r = JSON.parse(response);
			 console.log(data_r);
			 // data_r.forEach((line, i) => {
			 //
			 // });
			 $(line).remove();
		 });
	 }

	 function updateLine(line) {
		 let lines_data = {
			 'action': 'update_ads_txt_lines',
			 'data': {
				 "previous":$(line).find(".line-input").data("value"),
				 "new":$(line).find(".line-input").val()
			 }
		 }
		 $.post(ajaxurl, lines_data, function(response) {
			 let data_r = JSON.parse(response);
			 console.log(data_r);
		 });
	 }

	 function saveNewLines(lines) {
		 let new_lines_data = {
			 'action': 'save_ads_txt_new_lines',
			 'data': lines.val()
		 }
		 $.post(ajaxurl, new_lines_data, function(response) {
			 let data_r = JSON.parse(response);
			 lines.val("");
			 console.log(data_r);
			 addNewLines(data_r);
		 });
	 }

	 function toggleActive(radio) {
		 let toggle_active_data = {
			 'action': 'toggle_active_ads_txt',
			 'data': (true == radio[0].checked) ? radio[0].value : radio[1].value
		 }
		 $.post(ajaxurl, toggle_active_data, function(response) {
			 let data_r = JSON.parse(response);
			 console.log(data_r);
		 });
	 }

	 function launchLineModal(line){
		 let modal = document.createElement("div");
		 modal.id = "delete_modal";
		 modal.classList.add("modal");
		 modal.dataset.line = $(line).find(".line-input").val();

		 let modal_content = document.createElement("div");
		 modal_content.classList.add("modal-content");

		 let modal_close = document.createElement("span");
		 modal_close.innerHTML = "&times;";
		 modal_close.classList.add("close");

		 let modal_text = document.createElement("p");
		 modal_text.innerText = "Are you sure you want to delete this line?";

 		 let yes_button = document.createElement("button");
 		 yes_button.id = "modal-yes";
 		 yes_button.innerText = "Yes";

 		 let no_button = document.createElement("button");
 		 no_button.id = "modal-no";
 		 no_button.innerText = "No";

		 modal_content.append(modal_close, modal_text, yes_button, no_button);
		 modal.append(modal_content);

		 line.append(modal);

		 $(".modal .close, .modal #modal-no").on("click", function(e){
			 e.preventDefault();
			 $(".modal").remove();
		 });

 		 $(".modal #modal-yes").on("click", function(e){
			 e.preventDefault();
			 deleteLine(line);
 			 $(".modal").remove();
 		 });
	 }

})( jQuery );
