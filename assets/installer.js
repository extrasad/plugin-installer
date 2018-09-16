jQuery(document).ready(function ($) {

    var isLoading = false;
    var pluginsRecommended = ajax_object.plugins;
    var slugs = $('#plugin-slugs');
    var addRepositoryPluginBtn = $('#add-action');
    var inputSlug = $('#input-slug');
    var requiredBlock = $('#required-block');
    
    for (var i = 0; i < pluginsRecommended.length; i++) {
        var slug = `<li><label for="${pluginsRecommended[i]}">${pluginsRecommended[i]}</label><input type="checkbox" value="${pluginsRecommended[i]}" id="${pluginsRecommended[i]}" class="plugin-slug-selector"></li>`
        slugs.append(slug);
    }

    function addRepositoryPlugin() {
        const slug = inputSlug.val();

        if (!slug) {
            requiredBlock.show();
        } else {
            requiredBlock.hide();
            slugs.append(`
                <li>
                    <label for="${slug}">${slug}</label>
                    <input type="checkbox" value="${slug}" id="${slug}">
                </li>`
            );
        }
    }

    addRepositoryPluginBtn.on('click', function() {
        addRepositoryPlugin();
    });

    function getPlugins() {
        let plugins = [];

        $(".plugin-slug-selector:checked").each(function(){
            plugins.push($(this).val());
        });

        return plugins;
    }

    $('#install-action').on('click', function (e) {
        e.preventDefault();

        const plugins = getPlugins();

        isLoading = true;

        if (isLoading == true) {
            var wrapping = $('#load-spinner');
            wrapping.addClass('loader');
            $(this).prop('disabled', true);
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

                data.msg.map((item) => {
                    $('#list').append(`<li>${item}</li>`);
                });
                
                isLoading = false;
                $('#load-spinner').addClass('checkmark');

                setTimeout(function () {
                    $('#load-spinner').removeClass('loader');
                    $('#install-action').prop('disabled', false);
                }, 200);
            },
            error: function (data) {

                isLoading = false;
                $('#load-spinner').append('<span class="x-cancel">&#10007;</span>');
                $('#list').append('<li>Check if plugins that you tried to install where already installed, if you provided a wrong files path or if you wrote a bad slug for repositories\' plugins</li>');

                setTimeout(function () {
                    $('#load-spinner').removeClass('loader');
                }, 100);
            }
        });
    });
});