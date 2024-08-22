<?php
/*
* -------------------------------------------------------------------------------------
* @author: FP MoviesDB
* @author URI: https://fpmoviesdb.xyz/
* @copyright: (c) | All rights reserved
* -------------------------------------------------------------------------------------
*
* @since 1.0.0
*
*/

if (!defined('ABSPATH')) exit;

function fp_add_post_modified_date_to_publish_box()
{
    global $post;

    // $fp_post_modified = $post->fp_post_modified;
    // $display_post_modified = date('Y-m-d\TH:i', strtotime($fp_post_modified));
    // $formatted_post_modified = date('m/d/Y g:i A', strtotime($fp_post_modified));
    // $fp_post_modified_custom = get_post_meta($post->ID, '_fp_post_modified_custom', true);

    //log as string
    // fp_log_error('LOG AS STR: ' . print_r($post, true));

    $fp_post_modified = $post->fp_post_modified;

    // Check if the date is valid
    if (!empty($fp_post_modified) && strtotime($fp_post_modified) !== false) {
    } else {
        $fp_post_modified = $post->post_modified;
        // Provide a default or empty value if the date is not valid
        // $display_post_modified = '';
        // $formatted_post_modified = 'N/A';  // Or leave it empty
    }
    $display_post_modified = date('Y-m-d\TH:i', strtotime($fp_post_modified));
    $formatted_post_modified = date('m/d/Y g:i A', strtotime($fp_post_modified));

    $fp_post_modified_custom = get_post_meta($post->ID, '_fp_post_modified_custom', true);

?>
    <div class="misc-pub-section">
        <span class="dashicons dashicons-calendar"></span>
        <label for="fp_post_modified">Modified on:</label>
        <span id="post_modified_display" style="font-weight: 600;"><?php echo esc_html($formatted_post_modified); ?></span>
        <a href="#" id="edit_post_modified">Edit</a>
        <div id="fp_date_modify_wrapper" style="display: flex; gap: 2px; flex-direction: column; justify-content: start; align-items: center; display: none;">
            <input type="datetime-local" id="fp_post_modified" name="fp_post_modified" value="<?php echo esc_attr($display_post_modified); ?>" style="flex:1; min-width: 0px;" />
            <a href="#" id="reset_post_modified">Reset</a>
        </div>
        <input type="hidden" id="fp_post_modified_custom" name="fp_post_modified_custom" value="<?php echo esc_attr($fp_post_modified_custom ? '1' : '0'); ?>" />

    </div>

    <script type="text/javascript">
        document.getElementById('edit_post_modified').addEventListener('click', function(event) {
            event.preventDefault();

            toggleEditLink();
        });

        function toggleEditLink() {
            const editLink = document.getElementById('edit_post_modified');
            const dateModifyWrapper = document.getElementById('fp_date_modify_wrapper');

            if (editLink.textContent === 'Edit') {
                dateModifyWrapper.style.display = 'flex';
                editLink.textContent = 'Cancel';
            } else {
                dateModifyWrapper.style.display = 'none';
                editLink.textContent = 'Edit';
            }
        }


        document.getElementById('reset_post_modified').addEventListener('click', function(event) {
            event.preventDefault();

            const editLink = document.getElementById('edit_post_modified');
            const dateModifyWrapper = document.getElementById('fp_date_modify_wrapper');

            // Get current date and time in the required formats
            const now = new Date();
            const formattedPostModified = now.toLocaleString('en-US', {
                month: '2-digit',
                day: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                // second: '2-digit',
                hour12: true
            });
            const isoDate = new Date(now.getTime() - (now.getTimezoneOffset() * 60000)).toISOString();
            const displayPostModified = isoDate.substring(0, isoDate.length - 8);

            // Update display and input values
            document.getElementById('post_modified_display').textContent = formattedPostModified;
            document.getElementById('fp_post_modified').value = displayPostModified;

            // Set custom modified flag to '0' to indicate automatic update
            document.getElementById('fp_post_modified_custom').value = '0';
            dateModifyWrapper.style.display = 'none';
            toggleEditLink();
        });

        document.getElementById('fp_post_modified').addEventListener('change', function() {
            document.getElementById('fp_post_modified_custom').value = '1';
        });
    </script>

<?php
}
