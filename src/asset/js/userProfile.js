jQuery(document).ready(function($) {
    function set_gameplay_fields(selectedPageID) {
        $.ajax({
            url: gameplay.ajax_url,
            type: 'POST',
            dataType: 'json', 
            data: {
                action: 'change_select_page',
                nonce: gameplay.nonce,
                page_id: selectedPageID
            },
            success: function(response) {
                if (response.success) {
                    if (response.data.gameplay) {
                        $(`#acf-${gameplay.team_id_field_key}`).val(response.data.gameplay.team_id);
                        $(`#acf-${gameplay.minecraft_id_field_key}`).val(response.data.gameplay.minecraft_id);
                        $(`#acf-${gameplay.server_id_field_key}`).val(response.data.gameplay.server_id);
                        $(`#acf-${gameplay.game_id_field_key}`).val(response.data.gameplay.game_id);
                        $(`#acf-${gameplay.group_id_field_key}`).val(response.data.gameplay.group_id);
                    } else {
                        $(`#acf-${gameplay.team_id_field_key}`).val("");
                        $(`#acf-${gameplay.minecraft_id_field_key}`).val("");
                        $(`#acf-${gameplay.server_id_field_key}`).val("");
                        $(`#acf-${gameplay.game_id_field_key}`).val("");
                        $(`#acf-${gameplay.group_id_field_key}`).val("");
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

    $(`#acf-${gameplay.pages_field_key}`).on('change', function() {
        var selectedValue = $(this).val();
        set_gameplay_fields(selectedValue);
    });

    var selectedValue = $(`#acf-${gameplay.pages_field_key}`).val();
    set_gameplay_fields(selectedValue);
});