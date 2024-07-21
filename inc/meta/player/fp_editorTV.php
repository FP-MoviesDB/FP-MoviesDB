<?php if (!defined('ABSPATH')) exit; ?>
<!-- <form method="post" action=""> -->
    <table id="fp_video_table" class="fp_video_table">
        <thead class="fp-table-head">
            <tr>
                <th style="width: 40px;" class="fp-drag-head">#</th>
                <th style="width: 75px;">*Season</th>
                <th style="width: 75px;">*Episode</th>
                <th style="width: 150px;">*Title</th>
                <th style="flex-grow: 1;">*Source</th>
                <th style="width: 120px;">Language</th>
                <th style="width: 40px;">&#9881;</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($video_rows)) : ?>
                <?php
                foreach ($video_rows as $season => $episodes) :
                    foreach ($episodes as $episode => $details) :
                        foreach ($details as $index => $row) : ?>
                            <tr class="fp_video_row">
                                <td data-label="Drag" class="fp-drag-handle">&#x2630;</td>

                                <td data-label="season" style="width: 75px;">
                                    <input type="number" name="season[]" value="<?php echo esc_attr($row['season']); ?>" style="width: 75px;" placeholder="1">
                                </td>

                                <td data-label="episode" style="width: 75px;">
                                    <input type="number" name="episode[]" value="<?php echo esc_attr($row['episode']); ?>" style="width: 75px;" placeholder="1">
                                </td>

                                <td data-label="Title" style="width: 150px;">
                                    <input type="text" name="title[]" value="<?php echo esc_attr($row['title']); ?>" style="width: 150px;" placeholder="Title">
                                </td>

                                <td data-label="URL/Shortcode" style="flex-grow: 1;">
                                    <input type="text" name="url[]" value="<?php echo esc_attr($row['url']); ?>" style="width: 100%;" placeholder="iFrame URL">
                                </td>

                                <td data-label="Language" class="fp_player_selector">
                                    <select name="language[]">
                                        <?php
                                        $languages = languages();
                                        foreach ($languages as $label => $code) {
                                            $selected = ($row['language'] == $code) ? 'selected' : '';
                                            echo '<option value="' . esc_attr($code) . '" ' . $selected . '>' . esc_html($label) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </td>

                                <td data-label="delete" style="width: 40px;"><button type="button" class="fp_delete_row">X</button></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php else : ?>
                <tr class="fp_video_row">
                    <td data-label="Drag" class="fp-drag-handle">&#x2630;</td>
                    <td data-label="season" style="width: 75px;">
                        <input type="number" name="season[]" value="" style="width: 75px;" placeholder="1">
                    </td>
                    <td data-label="episode" style="width: 75px;">
                        <input type="number" name="episode[]" value="" style="width: 75px;" placeholder="1">
                    </td>

                    <td data-label="Title" style="width: 150px;">
                        <input type="text" name="title[]" value="" style="width: 150px;" placeholder="Title">
                    </td>

                    <td data-label="URL/Shortcode" style="flex-grow: 1;">
                        <input type="text" name="url[]" value="" style="width: 100%;" placeholder="iFrame URL">
                    </td>

                    <td data-label="Language" class="fp_player_selector">
                        <select name="language[]">
                            <?php
                            $languages = languages();
                            foreach ($languages as $label => $code) {
                                $selected = (null == $code) ? 'selected' : '';
                                echo '<option value="' . esc_attr($code) . '" ' . $selected . '>' . esc_html($label) . '</option>';
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

        function getNextSeasonEpisode(lastRow) {
            var season = parseInt(lastRow.find('input[name="season[]"]').val());
            var episode = parseInt(lastRow.find('input[name="episode[]"]').val()) + 1;
            if (isNaN(season)) season = 1;
            if (isNaN(episode)) episode = 1;
            return {
                season,
                episode
            };
        }

        function setupNewRow(row, lastRow) {
            var newValues = getNextSeasonEpisode(lastRow);
            row.find('input[name="season[]"]').val(newValues.season);
            row.find('input[name="episode[]"]').val(newValues.episode);
            row.find('input:not([name="season[]"], [name="episode[]"])').val('');
            row.find('input[name="title[]"]').val('Stream');
            row.find('select').eq(0).val(null);
        }

        $(document).on('click', '#fp_add_row', function() {
            var hiddenRows = $('#fp_video_table tbody tr:hidden');
            if (hiddenRows.length > 0) {
                var rowToShow = hiddenRows.first();
                rowToShow.css('display', '');
                setupNewRow(rowToShow, $('#fp_video_table tbody tr:visible:last'));
            } else {
                var lastRow = $('#fp_video_table tbody tr:visible:last');
                var row = lastRow.clone();
                setupNewRow(row, lastRow);
                row.appendTo('#fp_video_table tbody');
            }
        });

        $(document).on('click', '.fp_delete_row', function() {
            if ($('#fp_video_table tbody tr').length > 1) {
                $(this).closest('tr').remove();
            } else {
                $(this).closest('tr').css('display', 'none');
                $(this).closest('tr').find('input').val('');
                $(this).closest('tr').find('select').val(null);

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