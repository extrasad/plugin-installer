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

				preDownloadFromUrl(slugFromUrl);
			}
		}
	}

	function preDownloadFromUrl(slugFromUrl) {

		fetch('https://api.wordpress.org/plugins/info/1.0/' + slugFromUrl + '.json')
			.then(function(response) {
				return response.json();
			})
			.then(function(res) {
				const name = res.name;
				const downloadLink = res.download_link;
				const slug = res.slug;
				
				installBtn.prop('disabled', false);
				isLoading = false;
				checkIsLoading();
				downloadFromUrl(downloadLink, slug, name);
			})
			.catch(function(error) {
				console.log('Hubo un problema con la petición Fetch:' + error);
			});
	}

	function downloadFromUrl(downloadLink, slug, name) {

		const data = {
			'action': 'pinst_download_from_url',
			'download_link': downloadLink,
			'name': name,
			'slug': slug
		};
		const reportArea = $('.pinst__report');
		let isLoading = true;

		if (isLoading === true) {

			installBtn.prop('disabled', true);
		}


		$.ajax({
			type: 'post',
			url: ajaxurl,
			dataType: 'json',
			data,
			success: function (response) {
				isLoading = false;

				if (typeof response[0] === 'string') {

					reportArea.append('<p>' + response[0] + '</p>');

			 	}	else {
					console.log(response[0]);
				}

				if(!isLoading) {
					installBtn.prop('disabled', false);
				}
			},
			error: function (response) {
				isLoading = false;

				if(!isLoading) {

					installBtn.prop('disabled', false);
				}
			}
		});
	}

	function checkIsLoading() {
		if (!isLoading) {
			ladingSpinner.hide();
		} else {
			ladingSpinner.show();
		}
	}

	function getPluginInfo(slug) {

		const parentList = pluginsListNode;

		installBtn.prop('disabled', true);

		fetch('https://api.wordpress.org/plugins/info/1.0/' + slug + '.json')
			.then(function(response) {
				return response.json();
			})
			.then(function(res) {
				const name = res.name;
				const downloadLink = res.download_link;
				const slug = res.slug;

				parentList
				.append(`
					<li class="pinst__item" data-slug="${slug}" data-link="${downloadLink}" data-name="${name}">
						<span>${name}</span>
					</li>
				`);
				
				installBtn.prop('disabled', false);
				isLoading = false;
				checkIsLoading();
			})
			.catch(function(error) {
				console.log('Hubo un problema con la petición Fetch:' + error);
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
		
		const thisEl = $(element);
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
				isLoading = false;
				const loadingSpinner = thisEl.find('.showbox--inline');
				if (typeof response[0] === 'string') {
					reportArea.append('<p>' + response[0] + '</p>');

			 	}	else {
					response[0].map(function(res, index) {
						console.log(res);
					});
				}

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