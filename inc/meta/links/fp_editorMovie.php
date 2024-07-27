<?php if (!defined('ABSPATH')) exit; ?>
<table id="fp_links_table" class="fp_links_table">
    <thead class="fp-table-head">
        <tr>
            <th style="min-width: 40px;" class="fp-drag-head">#</th>
            <th style="min-width: 192px; flex-grow: 1;">*Title</th>
            <th style="min-width: 192px; flex-grow: 1;">*URL</th>
            <th style="min-width: 80px;">Quality</th>
            <th style="min-width: 100px;">Audio</th>
            <th style="min-width: 70px;">Size</th>
            <th style="min-width: 40px;">&#9881;</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($links_rows)) : ?>
            <?php foreach ($links_rows as $row) : ?>
                <?php 
                    // error_log('Row: ' . print_r($row, true)); ?>
                <tr class="fp_links_row">
                    <td data-label="Drag" class="fp-links-drag-handle">&#x2630;</td>
                    <td data-label="Title" style="min-width: 100px; flex-grow: 1;">
                        <input type="text" name="l_title[]" value="<?php echo esc_attr($row['l_title']); ?>" style="width: 100%;" placeholder="Title">
                    </td>
                    <td data-label="URL" style="min-width: 192px; flex-grow: 1;">
                        <input type="text" name="l_url[]" value="<?php echo esc_attr($row['l_url']); ?>" style="width: 100%;" placeholder="URL">
                    </td>
                    <td data-label="Quality" style="max-width: 80px;">
                        <select name="l_quality[]" class="link_selector" style="min-width: 80px;">
                            <?php
                            $qualities = link_selector();
                            foreach ($qualities as $label => $code) {
                                $selected = ($row['l_quality'] == $code) ? 'selected' : '';
                                echo '<option value="' . esc_attr($code) . '" ' . $selected . '>' . esc_html($label) . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                    <td data-label="Audio" style="width: 100px;">
                        <input type="text" name="l_audio[]" value="<?php echo isset($row['l_audio']) ? esc_attr($row['l_audio']) : ''; ?>" style="width: 100%;" placeholder="Audio">
                    </td>
                    <td data-label="Size" style="width: 70px;">
                        <input type="text" name="l_size[]" value="<?php echo isset($row['l_size']) ? esc_attr($row['l_size']) : ''; ?>" style="width: 100%;" placeholder="Size">
                    </td>
                    <td data-label="delete" style="width: 40px;"><button type="button" class="fp_link_delete_row">X</button></td>
                </tr>
                <?php // endforeach; 
                ?>
            <?php endforeach; ?>

        <?php else : ?>
            <tr class="fp_links_row">
                <td data-label="Drag" class="fp-links-drag-handle">&#x2630;</td>
                <td data-label="Title" style="min-width: 100px;  flex-grow: 1;">
                    <input type="text" name="l_title[]" style="width: 100%;" placeholder="Title">
                </td>
                <td data-label="URL" style="min-width: 192px;  flex-grow: 1;">
                    <input type="text" name="l_url[]" style="width: 100%;" placeholder="URL">
                </td>
                <td data-label="Quality" style="max-width: 80px;">
                    <select name="l_quality[]" class="link_selector" style="min-width: 80px;">
                        <?php
                        $qualities = link_selector();
                        foreach ($qualities as $label => $code) {
                            $selected = ($code == 144) ? ' selected' : '';
                            echo '<option value="' . esc_attr($code) . '"' . $selected . '>' . esc_html($label) . '</option>';
                        }
                        ?>
                    </select>
                </td>
                <td data-label="Audio" style="width: 100px;">
                    <input type="text" name="l_audio[]" style="width: 100%;" placeholder="Audio">
                </td>
                <td data-label="Size" style="width: 70px;">
                    <input type="text" name="l_size[]" style="width: 100%;" placeholder="Size">
                </td>
                <td data-label="delete" style="width: 40px;"><button type="button" class="fp_link_delete_row">X</button></td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
<!-- </form> -->
<div>
    <p><small>*Title and *URL are required fields.</small></p>

    <p style="margin: 0px; padding: 0px;">
        <b>Title accepted args to dynamic fill the title.</b><br />
    </p>

    <pre style="display: inline; padding: 0; margin: 0;">{title} {t_title} {year} {quality} {audio}</pre><br />

    <p style="margin: 0px; padding: 0px;">
        <small>{title}, {year} is post Title and Year.</small><br />
        <small>{quality}, {audio} is from current row.</small>
    </p>
</div>
<p class="add_new_link_wrapper">
    <a id="fp_add_link_row" class="add_link_row"> Add new row</a>
</p>



<script type="text/javascript">
    jQuery(document).ready(function($) {
        $('#fp_add_link_row').on('click', function() {
            var row = `
                <tr class="fp_links_row">
                    <td data-label="Drag" class="fp-links-drag-handle">&#x2630;</td>
                    <td data-label="Title" style="min-width: 100px; flex-grow: 1;">
                        <input type="text" name="l_title[]" style="width: 100%;" placeholder="Title">
                    </td>
                    <td data-label="URL" style="min-width: 192px; flex-grow: 1;">
                        <input type="text" name="l_url[]" style="width: 100%;" placeholder="URL">
                    </td>
                    <td data-label="Quality" style="max-width: 80px;">
                        <select name="l_quality[]" class="link_selector" style="min-width: 80px;">
                            <?php
                            $qualities = link_selector();
                            foreach ($qualities as $label => $code) {
                                $selected = ($code == 144) ? ' selected' : '';
                                echo '<option value="' . esc_attr($code) . '"' . $selected . '>' . esc_html($label) . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                    <td data-label="Audio" style="width: 100px;">
                        <input type="text" name="l_audio[]" style="width: 100%;" placeholder="Audio">
                    </td>
                    <td data-label="Size" style="width: 70px;">
                        <input type="text" name="l_size[]" style="width: 100%;" placeholder="Size">
                    </td>
                    <td data-label="delete" style="width: 40px;"><button type="button" class="fp_link_delete_row">X</button></td>
                </tr>
            `;
            $('#fp_links_table tbody').append(row);
        });

        $(document).on('click', '.fp_link_delete_row', function() {
            $(this).closest('.fp_links_row').remove();
        });

        // Check if jQuery UI's sortable function is available
        if (typeof $.fn.sortable === 'function') {
            $('#fp_links_table tbody').sortable({
                handle: '.fp-links-drag-handle',
                cursor: 'move',
                axis: 'y',
                opacity: 0.6
            });
        } else {
            console.error("Sortable function not available. Ensure jQuery UI is loaded.");
        }

    });
</script>





<style>
    .fp_links_table {
        width: 100%;
    }

    p.add_new_link_wrapper {
        padding: 10px 0px;
        width: 100%;
    }

    /* a.add_row {
        -webkit-box-shadow: none;
        box-shadow: none;
        outline: 0;
    } */

    a.add_link_row {
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
        -webkit-box-shadow: none;
        box-shadow: none;
        outline: 0;
    }

    .fp_links_row td {
        padding: 0px;
    }

    a.add_link_row:hover {
        background: #bce6ff;
    }

    .fp_links_table thead tr th {
        padding: 0px;
    }

    .fp_links_table thead tr {
        display: grid;
        grid-template-columns: 40px 0.7fr 1fr 80px 100px 70px 40px;
        justify-content: space-between;
        align-items: center;
        padding: 10px 0px;
        gap: 5px;
    }

    .fp_links_row {
        display: grid;
        grid-template-columns: 40px 0.7fr 1fr 80px 100px 70px 40px;
        align-items: center;
        justify-content: space-between;
        padding: 10px 0px;
        gap: 5px;
        /* overflow: auto; */
    }

    .fp-links-drag-handle {
        cursor: move;
        margin: 0px auto;
        text-align: center;
        width: 40px;
    }

    .fp_link_delete_row {
        cursor: pointer;
        width: 100%;
        padding: 4px 0px;
        font-size: 15px;
        background: #ff0000;
        color: #fff;
        font-weight: 600;
        border: none;
    }

    .fp_link_delete_row:hover {
        background: black;

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