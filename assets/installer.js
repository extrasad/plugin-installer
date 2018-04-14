jQuery(document).ready(function ($) {

    var isLoading = false;
    var plugins = ajax_object.plugins;
    var jsonPluginData = JSON.stringify(plugins);
    var slugs = $('#plugin-slugs');

    for (var i = 0; i < plugins.length; i++) {
        var slug = `<li>${plugins[i]}</li>`
        slugs.append(slug);
    }

    $('#localPluginsZip').change(function(event) {
        var localSlugs = $('#local-plugin-slugs');
        var dataLen = $('#localPluginsZip').prop("files").length;

        for (var x = 0; x < dataLen; x++) {
            var localSlug = `<li>${$('#localPluginsZip').prop("files")[x].name}</li>`
            localSlugs.append(localSlug);
        }
    });

    $('#install-action').on('click', function (e) {
        e.preventDefault();

        isLoading = true;

        if (isLoading == true) {
            var wrapping = $('#load-spinner');
            wrapping.addClass('loader');
        }

        $.ajax({
            type: 'post',
            url: ajaxurl,
            dataType: 'json',
            data: {
                'action': 'takePlugins',
                'plugins': jsonPluginData,
            },
            success: function (data) {
                isLoading = false;
                $('#load-spinner').addClass('checkmark');

                setTimeout(function () {
                    $('#load-spinner').removeClass('loader');
                }, 200);
            },
            error: function (data) {
                isLoading = false;
                $('#load-spinner').append('&#10007;');

                setTimeout(function () {
                    $('#load-spinner').removeClass('loader');
                }, 100);
            }
        });
    });

    $('#install-action2').on('click', function(f) {
        f.preventDefault();

        isLoading = true;

        if (isLoading == true) {
            var wrapping = $('#load-spinner2');
            wrapping.addClass('loader');
        }

        var data = new FormData();
        var localPluginsData = null;
        var dataLen = $('#localPluginsZip').prop('files').length;
        var pluginsName = [];

        for (var x = 0; x < dataLen; x++) {
            data.append("plugins_zip[]", $('#localPluginsZip').prop("files")[x]);
            pluginsName.push("plugins_zip[]", $('#localPluginsZip').prop("files")[x].name);
            console.log(data);
        }

        var jsonLocalPluginData = JSON.stringify(pluginsName);
        data.append('action', 'extractLocalPlugins');
        data.append('local_plugins', jsonLocalPluginData);

        if (isLoading == true) {
            var wrapping = $('#load-spinner2');
            wrapping.addClass('loader');
        }

        $.ajax({
            type: 'post',
            url: ajaxurl,
            dataType: 'json',
            data,
            processData: false,
            contentType: false,
            success: function (data) {
                isLoading = false;
                $('#load-spinner2').addClass('checkmark');

                setTimeout(function () {
                    $('#load-spinner2').removeClass('loader');
                }, 200);
            },
            error: function (data) {
                isLoading = false;
                $('#load-spinner2').append('&#10007;');

                setTimeout(function () {
                $('#load-spinner2').removeClass('loader');
                }, 100);
            }
        });
    });
});