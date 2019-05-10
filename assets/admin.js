jQuery(document).ready(function($) {

    $(document).on('click', '.p18a-thickbox', function(e){
        e.preventDefault();

        var id = $(this).data('id'),
            type = $(this).data('type'),
            content = $('[data-' + type + '="' + id + '"]').text();

        $('#p18a-response-window').val(content);

        tb_show(P18A[type], '#TB_inline?width=700&height=550&inlineId=p18a-window', false);

    });
    
    $(document).on('click', '#p18a-select-all', function(e){
        e.preventDefault();

        $('#p18a-response-window').select();
    });

    $(document).on('submit', '#p18a-api-test', function(e){

        e.preventDefault();

        var valid = true;

        $('.p18a-error-msg').remove();
      
        $('input, textarea').on('focus', function(){
            $(this).removeClass('p18a-success p18a-error');
        });

        $('input, textarea', this).each(function(){
            
            // test json
            if($(this).data('json') && $(this).val() !== '') {

                try {
                    var json = $.parseJSON($(this).val());
                }
                catch (err) {
                    $(this).addClass('p18a-error');
                    $(this).after('<div class="p18a-error-msg">' + err.message.replace('JSON.parse', 'Error') + '</div>');
                    valid = false;
                }

            }

            if($(this).hasClass('required') && $(this).val() == '') {
                $(this).addClass('p18a-error');
                valid = false;
            }

        });

        if(valid) {

            $('#p18a-json-response').val(P18A.working + '...');

            $.ajax({
                method: "POST",
                url: ajaxurl,
                data: {
                    action: "p18a_request",
                    nonce: P18A.nonce,
                    data : $(this).serialize()
                },
                dataType : 'json'
            }).done(function(response) {

                $('#p18a-json-response').val(response.data).removeClass('p18a-success p18a-error').addClass((response.status) ? 'p18a-success' : 'p18a-error');
                $('#p18a-json-response-hedaer').text(response.headers);
                
            });
            
        }

    });
    
});