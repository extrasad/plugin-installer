jQuery(document).ready(function( $ ) {
    $('#install-action').on('click', function(e){

        var isLoading;
        if (isLoading == true){
            var wrapping = $('.wrap');
            wrapping.append('<div class="loader"></div>');
        }

        var testArray = ['jetpack'];
        var jsondatica = JSON.stringify(testArray);

          $.ajax({
            type: 'post',
            url: ajaxurl,
            dataType: 'json',
            data: {
                'action': 'takePlugins',
                'plugins': jsondatica
            },
            success: function(data) {
                console.log(data.status);
                isLoading = false;
            },
            error: function(data) {
                console.log(data.status);
                isLoading = false;
            }
        });        
    });
    
	
});