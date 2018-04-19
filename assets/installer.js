jQuery(document).ready(function ($) {

    var isLoading = false;
    var plugins = ajax_object.plugins;
    var slugs = $('#plugin-slugs');

    for (var i = 0; i < plugins.length; i++) {
        var slug = `<li>${plugins[i]}</li>`
        slugs.append(slug);
    }

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
                'plugins': plugins,
            },
            success: function (data) {
                
                if (data.success){
                    data.msg.map((item) => {
                        $('#list').append(`<li>${item}<span class="checkmark"></span></li>`);
                    });
                }
                

                isLoading = false;
                $('#load-spinner').addClass('checkmark');

                setTimeout(function () {
                    $('#load-spinner').removeClass('loader');
                }, 200);
            },
            error: function (data) {

                isLoading = false;
                $('#load-spinner').append('&#10007;');
                $('#list').append('<li>One or more plugins were not installed properly</li>');

                setTimeout(function () {
                    $('#load-spinner').removeClass('loader');
                }, 100);
            }
        });
    });
});