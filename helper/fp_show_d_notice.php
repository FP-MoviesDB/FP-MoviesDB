<?php

if (!defined('ABSPATH')) exit;


function fp_display_d_notice()
{
    if (get_transient('fp_moviesdb_d_notice') === false) {
        wp_enqueue_script('jquery');
        $nonce = wp_create_nonce('fp_dismiss_notice_nonce');
?>
        <div class="d_notice notice notice-info is-dismissible">
            <p>If you're finding value in FP MoviesDB, please consider supporting its development. Your contribution helps fuel ongoing improvements and ensures that I can continue to make this plugin more effective for everyone. Your support means the world to me and the future of this project! <a href="https://fbgo.xyz/fpMoviesDB" target="_blank">Support me on Ko-Fi!</a></p>
            <p>
                <button class="button button-primary" data-days="10" data-url="fbgo.xyz/fpMoviesDB">Let's do it!</button>
                <button class="button button-secondary" data-days="14">I have already donated.</button>
                <button class="button" data-days="7">No, I don't want to.</button>
            </p>
        </div>
        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.d_notice button').forEach(function(button) {
                    button.addEventListener('click', function() {
                        var d = this.getAttribute('data-days');
                        var url = this.getAttribute('data-url');
                        jQuery.post(ajaxurl, {
                            action: 'fp_dismiss_d_notice',
                            silence_days: d,
                            nonce: '<?php echo esc_attr($nonce); ?>'
                        }, function(response) {
                            if (url && d === '10') {
                                window.open('https://' + url, '_blank');
                            }
                            jQuery('.d_notice').remove();
                        });
                    });
                });
            });
        </script>
<?php
    }
}
