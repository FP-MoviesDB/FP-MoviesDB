<?php if (!defined('ABSPATH')) exit; ?>
<!-- <form method="post" action="<?php // echo esc_attr(admin_url('admin-post.php')); ?>"> -->
    <table id="fp_video_table" class="fp_video_table">
        <thead class="fp-table-head">
            <tr>
                <th style="width: 40px;" class="fp-drag-head">#</th>
                <th style="width: 150px;">*Title</th>
                <!-- <th style="width: 120px;">Type</th> -->
                <th style="flex-grow: 1;">*Source</th>
                <th style="width: 120px;">Language</th>
                <th style="width: 40px;">&#9881;</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($video_rows)) : ?>
                <?php foreach ($video_rows as $row) : ?>
                    <tr class="fp_video_row">
                        <td data-label="Drag" class="fp-drag-handle">&#x2630;</td>
                        <td data-label="Title" style="width: 150px;">
                            <input type="text" name="title[]" value="<?php echo esc_attr($row['title']); ?>" style="width: 150px;" placeholder="Title">
                        </td>
                        <!-- <td data-label="Type" class="fp_player_selector"> -->
                            <!-- <select name="type[]"> -->
                                <?php
                                // $types = types_player_options(); // Fetch types options
                                // foreach ($types as $type => $label) {
                                    // $selected = ($row['type'] == $type) ? 'selected' : '';
                                    // echo "<option value='{$type}' {$selected}>{$label}</option>";
                                // }
                                ?>
                            <!-- </select> -->
                        <!-- </td> -->
                        <td data-label="URL/Shortcode" style="flex-grow: 1;">
                            <input type="text" name="url[]" value="<?php echo esc_attr($row['url']); ?>" style="width: 100%;" placeholder="URL/Shortcode">
                        </td>
                        <td data-label="Language" class="fp_player_selector">
                            <select name="language[]">
                                <?php
                                $languages = languages();
                                foreach ($languages as $label => $code) {
                                    $selected = ($row['language'] == $code) ? 'selected' : '';
                                    echo '<option value="' . esc_attr($code) . '" ' . esc_html($selected) . '>' . esc_html($label) . '</option>';
                                }
                                ?>
                            </select>
                        </td>
                        <td data-label="delete" style="width: 40px;"><button type="button" class="fp_delete_row">X</button></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr class="fp_video_row">
                    <td data-label="Drag" class="fp-drag-handle">&#x2630;</td>
                    <td data-label="Title" style="width: 150px;">
                        <input type="text" name="title[]" style="width: 150px;" placeholder="Title">
                    </td>

                    <td data-label="URL/Shortcode" style="flex-grow: 1;">
                        <input type="text" name="url[]" style="width: 100%;" placeholder="URL/Shortcode">
                    </td>
                    
                    <td data-label="Language" class="fp_player_selector">
                        <select name="language[]">
                            <?php
                            $languages = languages();
                            foreach ($languages as $label => $code) {
                                echo '<option value="' . esc_attr($code) . '">' . esc_html($label) . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                    <td data-label="delete" style="width: 40px;">
                        <button type="button" class="fp_delete_row">X</button>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
<!-- </form> -->

<p class="add_new_wrapper">
    <a id="fp_add_row" class="add_row"> Add new row</a>
</p>

<script type="text/javascript">
    jQuery(document).ready(function($) {

        $('#fp_add_row').click(function() {
            var hiddenRows = $('#fp_video_table tbody tr:hidden');
            if (hiddenRows.length > 0) {
                // If there are hidden rows, take the first hidden row, show it, and clear its values
                var rowToShow = hiddenRows.first();
                rowToShow.show().find('input').val('');
            } else {
                // If there are no hidden rows, clone the first visible row and append it
                var row = $('#fp_video_table tbody tr:visible:first').clone();
                row.find('input').val('');
                row.find('select').eq(0).val(null);
                row.appendTo('#fp_video_table tbody');
            }
        });

        $(document).on('click', '.fp_delete_row', function() {
            if ($('#fp_video_table tbody tr').length > 1) { // Prevent deleting if only one row exists
                $(this).closest('tr').remove();
            } else {
                $(this).closest('tr').css('display', 'none');
            }
        });

        $('#fp_video_table tbody').sortable({
            handle: '.fp-drag-handle',
            cursor: 'move',
            axis: 'y',
            opacity: 0.8
        });
    });
</script>



<style>
    .fp_video_table {
        width: 100%;
    }

    p.add_new_wrapper {
        padding: 10px 0px;
        width: 100%;
    }

    a.add_row {
        -webkit-box-shadow: none;
        box-shadow: none;
        outline: 0;
    }

    a.add_row {
        display: block;
        border: 1px solid #0076aa;
        padding: 7px;
        background: #fdfdfd;
        border-radius: 2px;
        text-align: center;
        font-weight: 400;
        cursor: pointer;
        -webkit-transition: background .3s;
        -moz-transition: background .3s;
        -ms-transition: background .3s;
        -o-transition: background .3s;
        transition: background .3s;
        text-decoration: none;
    }

    a.add_row:hover {
        background: #bce6ff;
    }

    .fp_video_table thead tr {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .fp_video_row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 5px;
        padding: 10px 0px;
    }

    .fp-drag-handle {
        cursor: move;
        margin: 0px auto;
        text-align: center;
        width: 40px;
    }

    .fp_delete_row {
        cursor: pointer;
        width: 100%;
        padding: 4px 0px;
        font-size: 15px;
        background: #ff0000;
        color: #fff;
        font-weight: 600;
        border: none;
    }

    .fp_delete_row:hover {
        background: black;

    }

    .fp_player_selector {
        max-width: 120px;
        /* background-color: gainsboro; */
    }

    .meta-box-sortables select {
        background-color: gainsboro;
    }

    /* Mobile css */
    @media (max-width: 750px) {
        .fp-table-head {
            display: none;
        }

        .fp-drag-handle {
            display: none;
        }

        .fp-drag-head {
            display: none;
        }

        .fp_video_row {
            flex-direction: column;
            justify-content: start;
            align-items: start;
        }

        .fp_video_row * {
            width: 100% !important;
        }

        .fp_delete_row {
            padding: 2px 0px;
            font-size: 20px;
            font-weight: 500;
        }
    }
</style>