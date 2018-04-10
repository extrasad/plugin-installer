jQuery(document).ready(function( $ ) {
    $('#install-action').on('click', function(e){

        var isLoading = true;

        if (isLoading == true){
            var wrapping = $('#load-spinner');
            wrapping.addClass('loader');
        }

        var plugins = ['jetpack'];
        var jsonPluginData = JSON.stringify(plugins);

        $.ajax({
            type: 'post',
            url: ajaxurl,
            dataType: 'json',
            data: {
                'action': 'takePlugins',
                'plugins': jsonPluginData
            },
            success: function(data) {
                console.log(data.status);
                isLoading = false;
                $('#load-spinner').html("").hide();
            },
            error: function(data) {
                console.log(data.status);
                isLoading = false;
                $('#load-spinner').html("").hide();
            }
        });
        
        // $.ajax({
        //     type: 'post',
        //     url: ajaxurl,
        //     dataType: 'json',
        //     data: {
        //         'action': 'plginstOptionsPage',
        //         'plugins_preview': jsonPluginData
        //     },
        //     success: function(){
                
        //     }
        // });
        
    });
    
	
});