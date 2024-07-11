jQuery(document).ready(function($) {
    var header_text = document.querySelector(`#acf-${param_enable.header_text_field_key}`);
    header_text.style.width = '400px';
    var info_text = document.querySelector(`#acf-${param_enable.info_text_field_key}`);
    info_text.style.width = '400px';

    function set_param_enable_fields(selectedPageID) {
        $.ajax({
            url: param_enable.ajax_url,
            type: 'POST',
            dataType: 'json', 
            data: {
                action: 'change_select_page',
                nonce: param_enable.nonce,
                page_id: selectedPageID
            },
            success: function(response) {
                if (response.success) {
                    if (response.data.param_enable) {
                        var header_text             = response.data.param_enable.header_text ? response.data.param_enable.header_text : 'QR Code Scanner';
                        var show_debug_data         = response.data.param_enable.show_debug_data ? response.data.param_enable.show_debug_data : 0;
                        var info_text               = response.data.param_enable.info_text ? response.data.param_enable.info_text : 'Click the picture above to scan QR code';
                        var team_id_enable          = response.data.param_enable.team_id_enable ? response.data.param_enable.team_id_enable : 0;
                        var minecraft_id_enable     = response.data.param_enable.minecraft_id_enable ? response.data.param_enable.minecraft_id_enable : 0;
                        var server_id_enable        = response.data.param_enable.server_id_enable ? response.data.param_enable.server_id_enable : 0;
                        var game_id_enable          = response.data.param_enable.game_id_enable ? response.data.param_enable.game_id_enable : 0;
                        var group_id_enable         = response.data.param_enable.group_id_enable ? response.data.param_enable.group_id_enable : 0;
                        var gamipress_ranks_enable  = response.data.param_enable.gamipress_ranks_enable ? response.data.param_enable.gamipress_ranks_enable : 0;
                        var gamipress_points_enable = response.data.param_enable.gamipress_points_enable ? response.data.param_enable.gamipress_points_enable : 0;

                        $(`#acf-${param_enable.header_text_field_key}`).val(header_text);
                        $(`#acf-${param_enable.show_debug_data_field_key}`).val(show_debug_data);
                        $(`#acf-${param_enable.info_text_field_key}`).val(info_text);
                        $(`#acf-${param_enable.team_id_enable_field_key}`).val(team_id_enable);
                        $(`#acf-${param_enable.minecraft_id_enable_field_key}`).val(minecraft_id_enable);
                        $(`#acf-${param_enable.server_id_enable_field_key}`).val(server_id_enable);
                        $(`#acf-${param_enable.game_id_enable_field_key}`).val(game_id_enable);
                        $(`#acf-${param_enable.group_id_enable_field_key}`).val(group_id_enable);
                        $(`#acf-${param_enable.gamipress_ranks_enable_field_key}`).val(gamipress_ranks_enable);
                        $(`#acf-${param_enable.gamipress_points_enable_field_key}`).val(gamipress_points_enable);
                    }
                } else {
                    alert('AJAX Error: ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error: ' + error);
            }
        });
    }

    $(`#acf-${param_enable.pages_field_key}`).on('change', function() {
        var selectedValue = $(this).val();
        set_param_enable_fields(selectedValue);
    });

    var selectedValue = $(`#acf-${param_enable.pages_field_key}`).val();
    // set_param_enable_fields(selectedValue);
});