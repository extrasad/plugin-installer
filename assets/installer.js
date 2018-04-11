jQuery(document).ready(function( $ ) {

    var plugins = ajax_object.plugins;
    var jsonPluginData = JSON.stringify(plugins);
    var slugs = $('#plugin-slugs');

    for (var i = 0; i < plugins.length; i++) {
        var slug = `<li>${plugins[i]}</li>`
        slugs.append(slug);
    }

    $('#install-action').on('click', function(e){

        var isLoading = true;

        if (isLoading == true){
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
            success: function(data) {
                isLoading = false;
                $('#load-spinner').addClass('checkmark');
Z
                setTimeout(function () { 
                    $('#load-spinner').removeClass('loader');
                }, 200);
            },
            error: function(data) {
                isLoading = false;
                $('#load-spinner').append('&#10007;');

                setTimeout(function () { 
                    $('#load-spinner').removeClass('loader');
                }, 100);

            }
        });

        // THIS IS AN AJAX INTENDED TO BE USED FOR LOCAL PLUGIN INSTALLATION

        /*$.ajax({
            type: 'post',
            url: ajaxurl,
            dataType: 'json',
            data: {
                'action': 'extractLocalPlugins',
                'my_directory': ajax_object.myDirectory,
                'extract_directory': ajax_object.extractTo
            },
            success: function(data) {
                console.log(data.status);
            },
            error: function(data) {
                console.log(data.status);
            }
        });     */
    });	
});