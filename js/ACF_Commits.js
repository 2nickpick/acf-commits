var ACF_Commits = {
    initialize_acf_field_group_form: function() {
        jQuery(window).load(function () {
            jQuery('#publish').prop('disabled', 'disabled');
        });
    },

    validate_commit_message: function(commit_message) {
        if(commit_message.length > 0 ) {
            jQuery('#publish').removeProp('disabled');
        } else {
            jQuery('#publish').prop('disabled', 'disabled');
        }
    },

    restore: function(post_id) {
        jQuery.post(
            ajaxurl,
            {
                'action': 'acf_commit_import',
                'post_id': post_id
            },
            function(response) {
                window.location.reload();
            });
    }
};
