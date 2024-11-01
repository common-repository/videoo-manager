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

	const adsTxtSdk = {
		run: function() {
			$("#ads-txt-lines").ready(function(){
				if (adsTxtMethods.isAdsTxtActive()) {
					adsTxtMethods.presentGroups(adsTxtData.show_groups_data);
					$("#add-new-group-line").on('click', function(e) {
						e.preventDefault();
						adsTxtHelpers.sanitizeFieldSet("#add-new-group-line-fieldset");
						if (adsTxtHelpers.isValidForm()) {
							adsTxtMethods.addSingleLine(adsTxtData.add_single_line_data(e.target));
							$(e.currentTarget).blur();
						}
					});
					$("#add-bulk-group-lines").on('click', function(e) {
						e.preventDefault();
						let bulk_lines = adsTxtMethods.checkBulkLines($(e.target).prev('#bulk-group-lines'));
						if(false == bulk_lines) return true;
						adsTxtMethods.addBulkLines(adsTxtData.add_bulk_lines_data(bulk_lines));
					});

					$("#create-group").on('click', function(e) {
						e.preventDefault();
						let modal_data = {
							"modal_action": e.currentTarget.id.split('-')[0],
							"group_data": adsTxtData.create_group(e)
						};
						adsTxtMethods.launchGroupModal(JSON.stringify(modal_data));
					});
				}


				$("#save-ads-txt-settings").on('click', function(e) {
					e.preventDefault();
					adsTxtMethods.toggleActive(adsTxtData.toggle_active_data($('.fieldset_input_radio_button input:checked').val()));
				});
			});
		}
	}

	const adsTxtData = {
		show_groups_data: {
			'action': 'show_groups',
			'data': '0'
		},
		add_single_line_data: function(line_data){
			return {
				'action': 'save_group_new_lines',
				'data': {
					"action" : "single_line",
					"group_name": document.querySelector('.group-lines').dataset.groupName,
					"line_data": {
						"domain": $(line_data).prevAll("#group-line-domain").val(),
						"network": $(line_data).prevAll("#group-line-network").val(),
						"type": $(line_data).prevAll("#group-line-type").val(),
						"certification": $(line_data).prevAll("#group-line-certification").val()
					},

				}
			}
		},
		add_bulk_lines_data: function(lines_data){
			return {
				'action': 'save_group_new_lines',
				'data':  {
					"action" : "bulk_lines",
					"group_name": document.querySelector('.group-lines').dataset.groupName,
					"lines_data": lines_data
				}
			}
		},
		delete_group_lines_data: function(lines){
			return {
				'action': 'delete_group_lines',
				'data': {
					"lines_data": lines
				}
			};
		},
		show_single_group_data: function(group_id){
			return {
				'action': 'show_single_group',
				'data': {
					'group_name': group_id
				}
			};
		},
		update_group_lines_data: function(lines){
			return {
				'action': 'update_group_lines',
				'data': {
					"lines_data": lines
				}
			};
		},
		delete_group: function(event) {
			return {
				'action': 'delete_group',
				'data': {
					'group_name': event.currentTarget.dataset.groupName
				}
			};
		},
		rename_group: function(event) {
			return {
				'action': 'rename_group',
				'data': {
					'group_name': event.currentTarget.dataset.groupName,
					'group_new_name': '',
				}
			};
		},
		create_group: function(event) {
			return {
				'action': 'create_group',
				'data': {
					'group_name': ''
				}
			};
		},
		toggle_active_data: function(value){
			return {
				'action': 'toggle_active_ads_txt',
				'data': {'config_active': value}
			}
		}
	}

	const adsTxtMethods = {
		isAdsTxtActive: function () {
			return 'yes' === $('.fieldset_input_radio_button input:checked').val();
		},
		presentGroups: function(data, id_show = null) {
			$.ajax(
				{
					url: ajaxurl,
					type: 'post',
					data: data,
					success: function(response) {
						let data_r = response;
						if(true === data_r.is_writable || null === data_r.is_writable || typeof data_r.is_writable === 'undefined') {
							adsTxtMethods.addGroups(data_r, id_show);
						}
					},
					fail: function(e) {
						console.error(e);
					}
				}
			);
		},
		addGroups: function(groups, id_show = null) {
			let groups_partial = $(".ads_txt #groups");
			let groups_container = document.createElement('div');
			groups_container.id = 'groups-container';
			groups_container.classList.add('groups-container');

			Object.entries(groups).forEach((group, i) => {
				let group_card = document.createElement('div');
				let group_card_title = document.createElement('h4');
				group_card.id = `group-id-${i}`;
				group_card.classList.add('group-card', `group-card-${i}`);
				group_card_title.innerText = group[1].group_name;
				group_card.append(group_card_title);
				group_card.dataset.groupId = `${group[1].group_name}`;
				groups_container.append(group_card);
				if (null === id_show) {
					id_show = group_card.dataset.groupId;
				}
			});
			adsTxtMethods.showGroup({
				'action': 'show_single_group',
				'data': {'group_name': id_show}
			});
			groups_partial.append(groups_container);
			$(".group-card, .group-card h3").on('click', function(e) {
				adsTxtMethods.showGroup(adsTxtData.show_single_group_data(e.currentTarget.dataset.groupId));
			});
			$("button").blur();
		 },
		showGroup: function(group_data) {
			$.post(ajaxurl, group_data, function(response) {
				let data_r = response;
				if (typeof data_r.validation_error !== 'undefined' && data_r.error !== null) {
					adsTxtHelpers.messageToast(".wrap.videoo-manager.ads_txt form .form-table", `${data_r.validation_error}`, ["notice", "notice-error"], 6000);
					return;
				}
				let group_lines_container = $('.group-lines');
				group_lines_container.html('');
				$('.groupform .action-buttons').remove();
				document.querySelector('.group-lines').dataset.groupName = data_r.group_name;
				$('.groupform h3.group-name').text(data_r.group_name.replace('#', ''));

				let disabled_lines_management = false;
				let disabled_group_management = false;
				if (data_r.group_name == '# Nogroup') {
					adsTxtHelpers.messageToast(".wrap.videoo-manager.ads_txt form .form-table", `<strong># Nogroup</strong> is an automatically generated group. You cannot add new lines to this group`, ["notice", "notice-warning"], 6000);
					disabled_group_management = true;
					if (data_r.group_lines === null) {
						disabled_lines_management = true;
					}
				}

				for (const line in data_r.group_lines) {
					let group_line_container = document.createElement('div');
					group_line_container.classList.add(`group-line-${line}`);
					group_line_container.id = `group-line-${line}`;
					adsTxtMethods.groupLineInputs(data_r.group_lines[line], line, group_line_container);
					group_lines_container.append(group_line_container);
				}
				adsTxtMethods.createHtmlFormButtons(data_r, disabled_lines_management, disabled_group_management);
			});
		 },
		createHtmlFormButtons: function (data_r, disabled_lines_management, disabled_group_management) {
			let group_lines_container = $('.group-lines');
			let disabled_lines = disabled_lines_management ? 'disabled' : '';
			let disabled_groups = disabled_group_management ? 'disabled' : '';
			let delete_selected = `<div class="action-buttons">
																<input type="checkbox" name="group-line-check-all" id="group-line-check-all" name="group-line-check-all" data-group="${btoa(data_r.group_name)}">
																<button ${disabled_lines} class="button button-primary delete-selected-group-lines" id="delete-selected-group-lines" data-group="${btoa(data_r.group_name)}">Delete selected lines <span class="dashicons dashicons-trash"></span></button>
															 	<button ${disabled_lines} class="button button-primary update-selected-group-lines" id="update-selected-group-lines" data-group="${btoa(data_r.group_name)}">Update selected lines <span class="dashicons dashicons-update-alt"></span></button>
															</div>`;
			let manage_group = `<filedset class="group-management action-buttons">
															<button ${disabled_groups} class="button button-primary half-button rename-group" id="rename-group" data-group-name="${data_r.group_name}">Rename Group <span class="dashicons dashicons-nametag"></span></button>
															<button ${disabled_groups} class="button button-primary half-button delete-group" id="delete-group" data-group-name="${data_r.group_name}">Delete Group <span class="dashicons dashicons-trash"></span></button>
														</filedset>`;
			$(group_lines_container).after(delete_selected);
			$('.groupform h3.group-name').after(manage_group);

			adsTxtMethods.addFormButtonEvents(data_r);

			$("#add-new-group-line").prop("disabled",disabled_group_management);
			$("#add-bulk-group-lines").prop("disabled",disabled_group_management);
		},
		addFormButtonEvents: function (data_r) {
			$('#delete-selected-group-lines').click(function(e) {
				e.preventDefault();
				let modal_data = {
					"modal_action": "delete",
					"group_name": data_r.group_name,
					"delete_lines": []
				};
				$(".group-line-fieldset input[type=checkbox]:checked").each(function(index){
					modal_data.delete_lines.push($(this).parent().data('line'));
				});
				(0 < modal_data.delete_lines.length) ? adsTxtMethods.launchLineModal(JSON.stringify(modal_data)) : adsTxtHelpers.messageToast(".wrap.videoo-manager.ads_txt form .form-table", `You need to check some lines to delete them!`, ["notice", "notice-warning"]);
			});
			$('#group-line-check-all').on('click', function(e) {
				$(".group-lines .group-line-fieldset input[type=checkbox]").each(function(item){
					$(this).trigger('click');
				});
			});
			$('#update-selected-group-lines').click(function(e) {
				e.preventDefault();
				let modal_data = {
					"modal_action": "update",
					"group_name": data_r.group_name,
					"update_lines": []
				};
				let isValid = true;
				$(".group-line-fieldset input[type=checkbox]:checked").each(function(index){
					let rowIndex = $(this).data("index");
					adsTxtHelpers.sanitizeFieldSet("#group-line-fieldset-" + rowIndex);
					if (adsTxtHelpers.isValidForm(rowIndex) !== true) {
						isValid = false;
					}
					modal_data.update_lines.push({
						"prev":$(this).parent().data('line'),
						"new": [
							$(this).siblings('input.group-line-domain').val(),
							$(this).siblings('input.group-line-network').val(),
							$(this).siblings('input.group-line-type').val(),
							$(this).siblings('input.group-line-certification').val()
						]
					});
				});
				if (isValid === true)
					(0 < modal_data.update_lines.length) ? adsTxtMethods.launchLineModal(JSON.stringify(modal_data)) : adsTxtHelpers.messageToast(".wrap.videoo-manager.ads_txt form .form-table", `You need to check some lines to update them!`, ["notice", "notice-warning"]);
			});

			$('#delete-group, #rename-group').on('click', function(e) {
				e.preventDefault();
				let modal_data = {
					"modal_action": e.currentTarget.id.split('-')[0],
					"group_data": (e.currentTarget.id == 'delete-group') ? adsTxtData.delete_group(e) : adsTxtData.rename_group(e)
				};
				adsTxtMethods.launchGroupModal(JSON.stringify(modal_data));

			});
		},
		launchGroupModal: function(modal_data){
			$("button#create-group, button#delete-group, button#rename-group").blur();
			let modal_object_data = JSON.parse(modal_data);
			let modal = document.createElement("div");
			modal.id = `${modal_object_data.modal_action}_group_modal`;
			modal.classList.add("modal");

			let modal_content = document.createElement("div");
			modal_content.classList.add("modal-content");

			let modal_close = document.createElement("span");
			modal_close.innerHTML = "&times;";
			modal_close.classList.add("close");

			let text_container = document.createElement('div');

			let modal_text = document.createElement("p");
			modal_text.innerText = `Are you sure you want to ${modal_object_data.modal_action} the group ${modal_object_data.group_data.data.group_name}?`;
			if('create' === modal_object_data.modal_action) modal_text.innerText = `Type the name for the new group to ${modal_object_data.modal_action}`;

			let new_name_input = document.createElement('input');
			new_name_input.type = `text`;
			new_name_input.name = `group-new-name`;
			new_name_input.id = `group-new-name`;
			new_name_input.placeholder = `${ ('create' === modal_object_data.modal_action) ? 'New group': modal_object_data.group_data.data.group_name}`;

			text_container.append(modal_text);
			if(/rename|create/.test(modal_object_data.modal_action)) text_container.append(new_name_input);

			let yes_button = document.createElement("button");
			yes_button.id = "modal-yes";
			yes_button.innerText = "Yes";

			let no_button = document.createElement("button");
			no_button.id = "modal-no";
			no_button.innerText = "No";


			modal_content.append(modal_close, text_container, yes_button, no_button);
			modal.append(modal_content);

			$('.group-lines').append(modal);

			$(new_name_input).focus();

			$(".modal .close, .modal #modal-no").on("click", function(e){
				e.preventDefault();
				$(".modal").remove();
			});
			$(document).on('keyup', function(event){
				if("Escape" === event.key) {
					$(".modal").remove();
				} else if ("Enter" === event.key) {
					event.preventDefault();
					if (/rename|create/.test(modal_object_data.modal_action) && adsTxtHelpers.isEmpty(new_name_input.value)) {
						adsTxtHelpers.markInvalidInput(new_name_input);
						setTimeout(() => {
							$(new_name_input).focus();
						}, 3000);
					} else {
						adsTxtMethods.resolveGroupModal(modal_object_data, new_name_input);
					}
				}
			});

			$(".modal #modal-yes").on("click", function(e){
				e.preventDefault();
				if (/rename|create/.test(modal_object_data.modal_action) && adsTxtHelpers.isEmpty(new_name_input.value)) {
					adsTxtHelpers.markInvalidInput(new_name_input);
					setTimeout(() => {
						$(new_name_input).focus();
					}, 3000);
				} else {
					adsTxtMethods.resolveGroupModal(modal_object_data, new_name_input);
				}
				//adsTxtMethods.resolveGroupModal(modal_object_data, new_name_input);
			});
		 },
		manageGroup: function(group_data) {
			$.ajax(
				{
					url: ajaxurl,
					type: 'post',
					data: group_data,
					success: function(response) {
						let data_r = response;
						if (typeof data_r.validation_error !== 'undefined' && data_r.error !== null) {
							adsTxtHelpers.messageToast(".wrap.videoo-manager.ads_txt form .form-table", `${data_r.validation_error}`, ["notice", "notice-error"], 6000);
							return;
						}
					 try {
						 if(data_r.created)adsTxtHelpers.messageToast(".wrap.videoo-manager.ads_txt .groups", `Group ${data_r.created} created.`, ["notice", "notice-success"]);
						 if(data_r.exists)adsTxtHelpers.messageToast(".wrap.videoo-manager.ads_txt .groups", `Group ${data_r.exists} already exists.`, ["notice", "notice-warning"]);
						 if(data_r.renamed)adsTxtHelpers.messageToast(".wrap.videoo-manager.ads_txt .groups", `Group ${data_r.renamed[0]} renamed to ${data_r.renamed[1]}.`, ["notice", "notice-success"]);
						 if(data_r.deleted)adsTxtHelpers.messageToast(".wrap.videoo-manager.ads_txt .groups", `Group ${data_r.deleted} deleted.`, ["notice", "notice-success"]);
						$(".ads_txt #groups #groups-container").remove();
						let group_data = data_r.created ? data_r.created : data_r.exists ? data_r.exists : data_r.renamed ? data_r.renamed[1] : 'Nogroup';
						adsTxtMethods.presentGroups(adsTxtData.show_groups_data, `# ${group_data}`);
					 } catch (error) {
						console.error(error);
						adsTxtHelpers.messageToast(".wrap.videoo-manager.ads_txt .groups", error, ["notice", "notice-error"]);
					 }
					},
					fail: function(e){
						console.error(e);
						adsTxtHelpers.messageToast(".wrap.videoo-manager.ads_txt .groups", error, ["notice", "notice-error"]);
					}
				});
		 },
		groupLineInputs: function(line, line_index, group_line_container) {
			let data_line = `${line.domain},${line.account},${line.type}${"" != line.certification ? ','+ line.certification : ''}`;

			let new_input = `<fieldset id="group-line-fieldset-${line_index}" class="group-line-fieldset previous${line.repeated ? ' repeated' : ''}" data-line="${data_line}"${line.repeated ? ' title="This line is duplicated"' : ''}>
												<input type="checkbox" data-index="${line_index}" name="group-line-check-${line_index}" id="group-line-check-${line_index}" name="group-line-check-${line_index}">
												<input type="text" name="group-line-domain-${line_index}" id="group-line-domain-${line_index}" class="group-line-domain" placeholder="Domain*" value="${line.domain}"required>
												<input type="text" name="group-line-network-${line_index}" id="group-line-network-${line_index}" class="group-line-network" placeholder="Account*" value="${line.account}"required>
												<input type="text" name="group-line-type-${line_index}" id="group-line-type-${line_index}" class="group-line-type" placeholder="Type*" value="${line.type}"required>
												<input type="text" name="group-line-certification-${line_index}" id="group-line-certification-${line_index}" class="group-line-certification" placeholder="Certification Authority" value="${line.certification}">
											 </fieldset>`;
			$(group_line_container).append(new_input);
		 },
		addSingleLine: function(new_line_data) {
			 $.ajax(
				{
					url: ajaxurl,
					type: 'post',
					data: new_line_data,
					success: function(response) {
						let data_r = response;
						try {
							$(".group-line-fieldset.newline input").val("");
							$('.group-lines').html('');

							Object.entries(data_r).forEach((data) => {
								if ("error" === data[0]) {
									console.error(data[1]);
									adsTxtHelpers.messageToast(".wrap.videoo-manager.ads_txt form .form-table", data[1][0], ["notice", "notice-error"]);
								} else {
									Object.entries(data[1]).forEach((d_item) => {
										adsTxtHelpers.messageToast(
											".wrap.videoo-manager.ads_txt form .form-table",
											`Line ${d_item[1].domain}, ${d_item[1].network}, ${d_item[1].type}${d_item[1].certification ? ', '+ d_item[1].certification : '' } <strong>${data[0].replace(/\_+?/g, ' ')}<strong>`,
											["notice", `notice-${'saved' === data[0] ? 'success' : 'already_exists' === data[0] ? 'warning' : 'error'}`]);
									});
								}
							});
							adsTxtMethods.showGroup({
								'action': 'show_single_group',
								'data': { 'group_name': document.querySelector('.group-lines').dataset.groupName }
							});
					 } catch (error) {
						console.error(error);
						adsTxtHelpers.messageToast(".wrap.videoo-manager.ads_txt form .form-table", error, ["notice", "notice-error"]);
					 }
					},
					fail: function(e){
						console.error(e);
						adsTxtHelpers.messageToast(".wrap.videoo-manager.ads_txt form .form-table", error, ["notice", "notice-error"]);
					}
				});
		 },
		addBulkLines: function(new_bulk_lines) {
			$.post(ajaxurl, new_bulk_lines, function(response) {
				let data_r = response;
				try {
					$("#bulk-group-lines").val("");
					$('.group-lines').html('');
					Object.entries(data_r).forEach((data) => {
						if ("error" === data[0]) {
							console.error(data[1]);
							adsTxtHelpers.messageToast(".wrap.videoo-manager.ads_txt form .form-table", data[1][0], ["notice", "notice-error"]);
						} else {
							Object.entries(data[1]).forEach((d_item) => {
								adsTxtHelpers.messageToast(
									".wrap.videoo-manager.ads_txt form .form-table",
									`Line ${d_item[1].domain}, ${d_item[1].network}, ${d_item[1].type}${d_item[1].certification ? ', '+ d_item[1].certification : '' } <strong>${data[0].replace(/\_+?/g, ' ')}<strong>`,
									["notice", `notice-${'saved' === data[0] ? 'success' : 'already_exists' === data[0] ? 'warning' : 'error'}`]);
							});
						}
					});
					adsTxtMethods.showGroup({
						'action': 'show_single_group',
						'data': { 'group_name': document.querySelector('.group-lines').dataset.groupName}
					});
			 } catch (error) {
				console.error(error);
				adsTxtHelpers.messageToast(".wrap.videoo-manager.ads_txt form .form-table", error, ["notice", "notice-error"]);
			 }
			});
		 },
		createLine: function(line_data, line_index) {
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
			//deleteLineBtn.innerText = "Delete";
			deleteLineBtn.innerHTML = '<span class="dashicons dashicons-trash" title="Delete"></span>';
			deleteLineBtn.classList.add("line-btn");
			deleteLineBtn.id = `delete_line_btn_${line_index}`;
			//updateLineBtn.innerText = "Save";
			updateLineBtn.innerHTML = '<span class="dashicons dashicons-saved" title="Save"></span>';
			updateLineBtn.classList.add("line-btn");
			updateLineBtn.id = `update_line_btn_${line_index}`;

			lineWrapper.append(line_input, deleteLineBtn, updateLineBtn);

			return lineWrapper;
		 },
		deleteGroupLine: function(lines_data) {
			$.post(ajaxurl, lines_data, function(response) {
				let data_r = response;
				try {
					if(data_r.deleted) {
						data_r.deleted.forEach((del, i) => {
							$(`fieldset[data-line="${del}"]`).parent().remove();
							adsTxtHelpers.messageToast(".wrap.videoo-manager.ads_txt form .form-table", `Line ${del} deleted`, ["notice", "notice-info"]);
						});
					} else if (data_r.validation_error) {
						adsTxtHelpers.messageToast(".wrap.videoo-manager.ads_txt form .form-table", data_r.validation_error, ["notice", "notice-error"]);
					}

			 } catch (error) {
				console.error(error);
				adsTxtHelpers.messageToast(".wrap.videoo-manager.ads_txt form .form-table", error, ["notice", "notice-error"]);
			 }
			});
		},
		updateGroupLine: function(lines_data) {
			let action_info = JSON.parse(lines_data.data.lines_data);
			$.post(ajaxurl, lines_data, function(response) {
				let data_r = response;
				try {
					Object.entries(data_r).forEach((data) => {
						if ("error" === data[0]) {
							console.error(data[1]);
							adsTxtHelpers.messageToast(".wrap.videoo-manager.ads_txt form .form-table", data[1][0], ["notice", "notice-error"]);
						} else {
							Object.entries(data[1]).forEach((d_item) => {
								adsTxtHelpers.messageToast(
									".wrap.videoo-manager.ads_txt form .form-table",
									`Line ${d_item[1].prev} <strong>${data[0].replace(/\_+?/g, ' ')}<strong>`,
									["notice", `notice-${'updated' === data[0] ? 'info' : 'not_updated' === data[0] ? 'warning' : 'error'}`]
								);
								adsTxtMethods.showGroup(adsTxtData.show_single_group_data(action_info.group_name));
							});
						}
					});
					$(".group-lines input:checked").trigger('click');
				} catch (error) {
					console.error(error);
					adsTxtHelpers.messageToast(".wrap.videoo-manager.ads_txt form .form-table", error, ["notice", "notice-error"]);
				}
			});
		},
		launchLineModal: function(data) {
			$("button#delete-selected-group-lines, button#update-selected-group-lines").blur();
			let o_data = JSON.parse(data);
			let modal = document.createElement("div");
			modal.id = `${o_data.modal_action}_modal`;
			modal.classList.add("modal");

			let modal_content = document.createElement("div");
			modal_content.classList.add("modal-content");

			let modal_close = document.createElement("span");
			modal_close.innerHTML = "&times;";
			modal_close.classList.add("close");

			let modal_text = document.createElement("p");
			let lines_length = ("delete" == o_data.modal_action) ? o_data.delete_lines.length : o_data.update_lines.length;
			modal_text.innerText = `Are you sure you want to ${o_data.modal_action} this line${1 < lines_length ? 's' : ''}?`;

			 let yes_button = document.createElement("button");
			 yes_button.id = "modal-yes";
			 yes_button.innerText = "Yes";

			 let no_button = document.createElement("button");
			 no_button.id = "modal-no";
			 no_button.innerText = "No";

			modal_content.append(modal_close, modal_text, yes_button, no_button);
			modal.append(modal_content);

			$('.group-lines').append(modal);

			$(".modal .close, .modal #modal-no").on("click", function(e){
				e.preventDefault();
				$(".modal").remove();
			});
			$(document).on('keyup', function(event){
				if("Escape" === event.key) {
					$(".modal").remove();
				} else if ("Enter" === event.key) {
					adsTxtMethods.resolveLineModal(o_data.modal_action, data);
				}
			});

			 $(".modal #modal-yes").on("click", function(e){
				e.preventDefault();
				adsTxtMethods.resolveLineModal(o_data.modal_action, data);
			 });
		},
		checkBulkLines: function(textarea) {
			let array_lines = [];
			textarea.val().split('\n').filter(line => "" !== line.trim()).forEach((line, i) => {
				let matches = line.match(/^ *(,|.*?) *, *(,|.*?) *, *(DIRECT|RESELLER) *,?( *(,|.*?))?$/gmi);
				// let line_re = /^(\s)?((^((https?):\/\/)?([A-z0-9]{1,256}\.)?[-a-zA-Z0-9@:%_\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&//=]*\s)?),?\s?)((\s)?([-a-zA-Z0-9()@:%_\+.~#?&//=]*)?(\s)?,?\s?)(\s?(DIRECT|RESELLER),?\s?)(.*)+$/ig;
				let line_re = /^(,|\s)?((^((,|http(s)?)(,|:)?(,|\/)+)?([A-z0-9,]{1,256}\.)?[-a-zA-Z0-9@:%_\+~#=,]{1,256}\.[a-zA-Z0-9(),]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&//=,]*\s)?),?\s?)((\s)?([-a-zA-Z0-9()@:%_\+.~#?&//=,]*)?(\s)?,+?\s?)(\s?(DIRECT|RESELLER),?\s?)(.*)+$/img;
				let line_matches = line_re.exec(line);
				let toastMessage = 'Check your line, please: ';
				let invalid_inputs = 0;
				let re_coma = /\,/img;
				if (!line_matches) {
					toastMessage += `<strong>${line}</strong> is not a valid line. `;
					invalid_inputs++;
				} else {
					line_matches.forEach((match, i) => {
						if(match === line_matches[3]) {
							toastMessage += !adsTxtHelpers.validateDomain(match.replace(re_coma,'')) ? `${match} is not a valid domain. `: '';
						}
						if ("undefined" !== typeof match && ("" === match.replace(re_coma,'') && (3 == i || 11 == i || 15 == i)) ) {
							toastMessage += `${match} is empty. `;
							invalid_inputs++;
						}
					});
				}
				if(0 < invalid_inputs) {
					adsTxtHelpers.markInvalidInput(textarea);
					adsTxtHelpers.messageToast(".wrap.videoo-manager.ads_txt form .form-table", toastMessage, ['notice', 'notice-error']);
					// return false;
				} else {
					array_lines.push({
						"domain": line_matches[3].replace(re_coma,''),
						"network": line_matches[11].replace(re_coma,''),
						"type": line_matches[15].replace(re_coma,''),
						"certification": line_matches[17].replace(re_coma,'') ?? ''
					});
				}
			});
			return array_lines;
		},
		toggleActive: function(toggle_active_data) {
			$.ajax(
				{
					url: ajaxurl,
					type: 'post',
					data: toggle_active_data,
					success: function() {
						adsTxtHelpers.messageToast(".wrap.videoo-manager.ads_txt form .form-table", "Ads.txt status updated", ['notice', 'notice-success']);
						location.reload();
				},
				fail: function(e){
						adsTxtHelpers.messageToast(".wrap.videoo-manager.ads_txt form .form-table", e, ['notice', 'notice-error']);
					}
				}
			);
		},
		resolveGroupModal: function(data, input) {
			if ("rename" === data.modal_action) data.group_data.data.group_new_name = input.value;
			if ("create" === data.modal_action) data.group_data.data.group_name = input.value;
			adsTxtMethods.manageGroup(data.group_data);
			$(".modal").remove();
			$(document).off('keyup');
		},
		resolveLineModal: function(action, data) {
			if ('delete' == action) {
				adsTxtMethods.deleteGroupLine(adsTxtData.delete_group_lines_data(data));
			} else if ('update' == action) {
				adsTxtMethods.updateGroupLine(adsTxtData.update_group_lines_data(data));
			}
			 $(".modal").remove();
			 $(document).off('keyup');
		}

	}

	const adsTxtHelpers = {
		sanitizeFieldSet: function (fieldSet) {
			$(fieldSet + " input").each(function() {
				if ('' !== $(this).val() && null !== $(this).val())
					$(this).val($(this).val().replace(/[\s|,|\t]/g, ''));
			});
		},
		isValidForm: function (index) {
			let result = true;
			let suffix = typeof index !== 'undefined' ? '-' + index : '';
			if(true != adsTxtHelpers.validateDomain($("#group-line-domain" + suffix).val())) {
				adsTxtHelpers.markInvalidInput("#group-line-domain" + suffix);
				result = false;
			}
			if('' === $("#group-line-network" + suffix).val()) {
				adsTxtHelpers.markInvalidInput("#group-line-network" + suffix);
				result = false;
			}
			let re_type = /^(DIRECT|RESELLER)$/i;
			if($("#group-line-type" + suffix).val().trim().match(re_type) === null) {
				adsTxtHelpers.markInvalidInput("#group-line-type" + suffix);
				result = false;
			}
			return result;
		},
		validateDomain: function(domain) {
			let url_regex = /^((http(s)?):\/\/)?(([A-z0-9\-\_]+){1,256}\.)?([A-z0-9\-\_]+){1,256}\.([A-z0-9@:%._\\/+~\-#=\?\"\']{2,})/img;
			return url_regex.test(domain);
		},
		markInvalidInput: function(input) {
			$(input).addClass('validate');
			setTimeout(() => {
				$(input).removeClass('validate');
			}, 5000);
			return true;
		},
		messageToast: function(element, message, classes, timeout = 3000) {
			// TODO Chequear si ya existe la tostada
			let toast = document.createElement("div");
			classes.map((cls) => toast.classList.add(cls));
			toast.classList.add('is-dismissible');
			toast.innerHTML = `<p>${message}</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Descartar este aviso.</span></button>`;
			$(element).prepend(toast);

			if (0 < timeout) {
				setTimeout(() => {
					$(toast).remove();
				}, timeout);
			}
			$('button.notice-dismiss, button.notice-dismiss > span').on('click', function(e) {
				$(e.target).closest('.notice').remove();
			});
		 },
		 isEmpty: function(str) {
			return "" === str.trim();
		 }
	}

	try {
		adsTxtSdk.run();
	} catch (error) {
		console.log(error);
	}

})( jQuery || {jQuery});
