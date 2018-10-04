jQuery(document).ready(function ($) {

	const pluginsSlugs = pinst_processor.plugins;
	const pluginsAllowed = pinst_processor.plugins_allowed;
	const pluginUrl = pinst_plugin.url;
	const installBtn = $('#install-button');
	const pluginsListNode = $('.pinst__plugins > ul');
	const ladingSpinner = $('.showbox');
	const inputUrl = $('#url-request');
	const downloadFromUrlBtn = $('#download-button');
	const inputTextContainer = $('#url-request-container');
	const actionBtns = $('.pinst__button')
	let isLoading = true;


	// Events  --------------------------------- //

	installBtn.on('click', function(e) {
		wrapPluginsToInstall();
	});

	downloadFromUrlBtn.on('click', function(e) {
		processUrl();
	});

	// ------------------------------------------- //

	async function loadInitialData(e) { 
		checkIsLoading();

		for (let i = 0; i < pluginsSlugs.length; i++) {
			await	getPluginInfo(pluginsSlugs[i]);
		}

	};

	function processUrl() {
		const url = inputUrl.val();

		if (!url) {
			inputTextContainer.append('<p class="warning-text">Input required to search plugin.</p>');
		} else {
			const warningText = inputTextContainer.find('.warning-text');

			if (inputTextContainer) {
				warningText.remove();
			}
			
			const slugFromUrl = url.split('/').reverse()[1];
			const warningPlugin = inputTextContainer.find('.warning-plugin');

			if (!pluginsAllowed.includes(slugFromUrl)) {

				inputTextContainer.append('<p class="warning-plugin">' + slugFromUrl + ' is not allowed to be installed</p>');

			} else {
				warningPlugin.remove();

				getPluginInfo(slugFromUrl, true);
			}
		}
	}

	function checkIsLoading() {
		if (!isLoading) {
			ladingSpinner.hide();
			actionBtns.prop('disabled', false);
		} else {
			ladingSpinner.show();
			actionBtns.prop('disabled', true);
		}
	}

	function getPluginInfo(slug, externalUrl) {

		const parentList = pluginsListNode;
		const conditionalAjax = !externalUrl ? false : true;

		installBtn.prop('disabled', true);

		fetch('https://api.wordpress.org/plugins/info/1.0/' + slug + '.json')
			.then(function(response) {
				return response.json();
			})
			.then(function(res) {
				const name = res.name;
				const downloadLink = res.download_link;
				const slug = res.slug;

				if (!conditionalAjax) {

					parentList
					.append(`
						<li class="pinst__item" data-slug="${slug}" data-link="${downloadLink}" data-name="${name}">
							<span>${name}</span>
						</li>
					`);
					
					installBtn.prop('disabled', false);
					isLoading = false;
					checkIsLoading();

				} else {

					installPlugin(downloadLink, slug, name, false);

				}

			})
			.catch(function(error) {
				console.log('Hubo un problema con la petición fetch:' + error);
			});
	}

	function wrapPluginsToInstall() {
		const pluginNodes = document.getElementsByClassName('pinst__item');

		for (let i = 0; i < pluginNodes.length; i++) {
			const element = pluginNodes.item(i);
			const elSlug = element.dataset.slug;
			const elName = element.dataset.name;
			const elDownloadLink = element.dataset.link;

			installPlugin(elDownloadLink, elSlug, elName, element);
			
		}
	}

	// Download Trigger

	function installPlugin(downloadLink, slug, name, element) {
		
		let thisEl;

		if (!element) {
			thisEl = $('.pinst__external-download');
		} else {
			thisEl = $(element);
		}

		const pluginName = name;
		const data = {
			'action': 'pinst_process_plugin',
			'download_link': downloadLink,
			'slug': slug,
			'name': name
		};
		const reportArea = $('.pinst__report');

		let isLoading = true;

		if (isLoading === true) {
			thisEl.append(`
				<div class="showbox showbox--inline">
					<div class="loader loader--inline">
						<svg class="circular" viewBox="25 25 50 50">
							<circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10"/>
						</svg>
					</div>
				</div>
			`);

			installBtn.prop('disabled', true);
		}


		$.ajax({
			type: 'post',
			url: ajaxurl,
			dataType: 'json',
			data,
			success: function (response) {
				console.log(response);

				const data = response[0];
				const loadingSpinner = thisEl.find('.showbox--inline');

				if (data.status === 'failed') {
					reportArea.append('<p class="pinst__failed">' + data.msg + '</p>');
				} else {
					reportArea.append('<p class="pinst__success">' + data.msg + '</p>');
				}

				isLoading = false;

				if(!isLoading) {
					loadingSpinner.children().remove();
					loadingSpinner.append(`
						<img src="${pluginUrl}/admin/icons/baseline-done-24px.svg" alt="Done Icon">
					`);
					installBtn.prop('disabled', false);
				}

				
			},
			error: function (response) {
				isLoading = false;
				const loadingSpinner = thisEl.find('.showbox showbox--inline');
				
				reportArea.append('<p class="pinst__failed">'+ pluginName + ' could not be installed due a bad connection with server.</p>');

				if(!isLoading) {
					loadingSpinner.children().remove();
					loadingSpinner.append(`
						<img src="${pluginUrl}/admin/icons/baseline-close-24px.svg" alt="Done Icon">
					`);
					installBtn.prop('disabled', false);
				}
			}
		});
	}

	function removePrefix(prefix,s) {
    return s.substr(prefix.length);
	}


	// Load Initial data on document Ready

	loadInitialData();
});