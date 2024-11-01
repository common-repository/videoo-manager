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
	const configSdk =  {
		run: function () {
			$("#save-videoo_config-settings").ready(function() {
				configSdk.registerEvents();
				let integrationId = $("#videoo_manager_config_id").val();
				if (typeof integrationId !== 'undefined' && true === configHelpers.headContentFile(configHelpers.getUrlTag(integrationId))) {
					configSdk.previsualize(integrationId);
				}
			});
		},
		registerEvents: function () {
			$("#save-videoo_config-settings").on("click", function(e) {
				e.preventDefault();
				if (configSdk.isValidForm()) {
					let radio = $("#videoo_manager_config_active_yes, #videoo_manager_config_active_no");
					if (false == radio[0].checked) {
						configSdk.launchSubmitModal(configSdk.submitForm);
					} else {
						configSdk.submitForm();
					}
				}
			});
		},
		isValidForm: function () {
			let result = true;
			let integrationId = $("#videoo_manager_config_id").val();
			let paraGraph = $("#videoo_manager_config_position").val();
			const integrationIdFail = function () {
				configHelpers.messageToast(".wrap.videoo-manager.config form .form-table", `Your integration id doesn't exists. Please contact us for activate`, ["notice", "notice-error"], 0);
				$("#videoo_manager_config_active_no").prop("checked", true);
				return false;
			};

			if ("" !== integrationId && null != integrationId) {
				let existsTag = configHelpers.headContentFile(configHelpers.getUrlTag(integrationId));
				if (existsTag !== true) {
					result = integrationIdFail();
				}
			} else {
				result = result = integrationIdFail();
			}

			if (paraGraph === null || paraGraph === '' || paraGraph < 1 || paraGraph > 10) {
				configHelpers.messageToast(".wrap.videoo-manager.config form .form-table", `Paragraph number is a mandatory field and its value must be a number between 1 and 10`, ["notice", "notice-error"], 0);
				result = false;
			}
		 	return result;
		},
		submitForm: function () {
			let radio = $("#videoo_manager_config_active_yes, #videoo_manager_config_active_no");
			let value = (true == radio[0].checked) ? radio[0].value : radio[1].value;
			let config_data = {
				'action' : 'save_videoo_config_settings',
				'data': {
					'config_id': $("#videoo_manager_config_id").val(),
					'config_position': $("#videoo_manager_config_position").val(),
					'config_active': value
				}
			};
			$.post(ajaxurl, config_data, function(response) {
				let data_r = response;
				console.log(data_r);
				location.reload();
			});
		},
		launchSubmitModal: function(_callback){
			let modal = document.createElement("div");
			modal.id = `submit_modal`;
			modal.classList.add("modal");

			let modal_content = document.createElement("div");
			modal_content.classList.add("modal-content");

			let modal_close = document.createElement("span");
			modal_close.innerHTML = "&times;";
			modal_close.classList.add("close");

			let modal_text = document.createElement("p");
			modal_text.innerText = `VideooTV tag is disabled. This means that you will not get any revenue from VideooTV network on your website... Are you sure yoy want save this settings?`;

			let yes_button = document.createElement("button");
			yes_button.id = "modal-yes";
			yes_button.innerText = "Yes";

			let no_button = document.createElement("button");
			no_button.id = "modal-no";
			no_button.innerText = "No";

			modal_content.append(modal_close, modal_text, yes_button, no_button);
			modal.append(modal_content);

			$('.wrap.videoo-manager.config').append(modal);

			$(".modal .close, .modal #modal-no").on("click", function(e){
				e.preventDefault();
				$(".modal").remove();
			});

			$(".modal #modal-yes").on("click", function(e){
				e.preventDefault();
				_callback();
				$(".modal").remove();
			});
		},
		previsualize: function (integrationId) {
			let script = document.createElement('script');
			script.defer = true;
			script.src = configHelpers.getUrlTag(integrationId);
			script.id = 'videoo-library';
			script.dataset.id = integrationId;
			document.getElementById("videoo-manager-screen").appendChild(script);
		}
	}

	const configHelpers = {
		getUrlTag: function (id) {
			return 'https://static.videoo.tv/' + id + '.js';
		},
		headContentFile: function (url) {
			try {
				let xhttp = new XMLHttpRequest();
				xhttp.open('HEAD', url, false);
				xhttp.send();
				if (xhttp.status === 200) {
					return true;
				} else {
					return false;
				}
			} catch (error) {
				return false;
			}
		},
		messageToast: function(element, message, classes, timeout = 3000) {
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
		}
	}

	configSdk.run();


})( jQuery );
