<div id="kanban-wrapper" class="row">
        <?php
        $columns_data = array();

        $column_width = (335 * $total_columns) + 5;

        foreach ($tasks as $task) {

            $exising_items = get_array_value($columns_data, $task->status_id);
            if (!$exising_items) {
                $exising_items = "";
            }

            $task_labels = "";
            if ($task->labels) {
                $labels = explode(",", $task->labels);
                foreach ($labels as $label) {
                    $task_labels .= "<span class='label label-info'>" . $label . "</span> ";
                }
            }

            if (isset($task->artist_signoff) && !empty($task->artist_signoff)) {
                $task_labels .= "<span class='label' style='background:".$task->artist_signoff."'>" . lang('artist_signoff') . "</span> ";
            } 

            if (isset($task->final_signoff) && !empty($task->final_signoff)) {
                $task_labels .= "<span class='label' style='background:".$task->final_signoff."'>" . lang('final_signoff') . "</span> ";
            }

            if ( isset($task->deadline) ) {
                $task_labels .= "<span class='label label-default deadline'>" . date('d/m/y', strtotime($task->deadline)) . "</span>";
            }

            if ($task_labels) {
                $task_labels = "<div class='meta'>$task_labels</div>";
            }

            $item = $exising_items .  modal_anchor(get_uri("projects/task_view"), 
                        "<span class='avatar'>" .
                        "<img src='" . get_avatar($task->assigned_to_avatar) . "'>" .
                        "</span>" . $task->id . ". " . $task->title .
                        $task_labels,
                    array("class"=>"kanban-item", "data-id"=>$task->id, "data-project_id"=>$task->project_id, "data-sort"=>$task->new_sort, "data-post-id" => $task->id, "title" => lang('task_info') . " #$task->id",));

            $columns_data[$task->status_id] = $item;
        }
        ?>

        <ul id="kanban-container" class="kanban-container clearfix" style="width: <?php echo $column_width; ?>px;">

            <?php foreach ($columns as $column) { ?>
                <li class="kanban-col" >
                    <div class="kanban-col-title" style="background: <?php echo $column->color ? $column->color : "#2e4053"; ?>;"> <?php echo $column->key_name ? lang($column->key_name) : $column->title; ?> </div>

                    <div class="kanban-input general-form hide">
                        <?php
                        echo form_input(array(
                            "id" => "title",
                            "name" => "title",
                            "value" => "",
                            "class" => "form-control",
                            "placeholder" => lang('add_a_task')
                        ));
                        ?>
                    </div>

                    <div  id="kanban-item-list-<?php echo $column->id; ?>" class="kanban-item-list" data-status_id="<?php echo $column->id; ?>">
                        <?php echo get_array_value($columns_data, $column->id); ?>
                    </div>
                </li>
            <?php } ?>

        </ul>
    </div>

    <img id="move-icon" class="hide" src="<?php echo get_file_uri("assets/images/move.png"); ?>" alt="..." />
    
<script type="text/javascript">


    adjustViewHeightWidth = function () {

        //set wrapper scroll
        if ($("#kanban-wrapper")[0].offsetWidth < $("#kanban-wrapper")[0].scrollWidth) {
            $("#kanban-wrapper").css("overflow-x", "scroll");
        } else {
            $("#kanban-wrapper").css("overflow-x", "hidden");
        }


        //set column scroll 
        $(".kanban-item-list").height($(window).height() - $(".kanban-item-list").offset().top-30);
    
        $(".kanban-item-list").each(function (index) {

            //set scrollbar on column... if requred
            if ($(this)[0].offsetHeight < $(this)[0].scrollHeight) {
                $(this).css("overflow-y", "scroll");
            } else {
                $(this).css("overflow-y", "hidden");
            }

        });
    };


    saveStatusAndSort = function ($item, status) {
        appLoader.show();
        adjustViewHeightWidth();

        var $prev = $item.prev(),
                $next = $item.next(),
                prevSort = 0, nextSort = 0, newSort = 0,
                step = 100000, stepDiff = 500,
                id = $item.attr("data-id"),
                project_id = $item.attr("data-project_id");

        if ($prev && $prev.attr("data-sort")) {
            prevSort = $prev.attr("data-sort") * 1;
        }

        if ($next && $next.attr("data-sort")) {
            nextSort = $next.attr("data-sort") * 1;
        }


        if (!prevSort && nextSort) {
            //item moved at the top
            newSort = nextSort - stepDiff;

        } else if (!nextSort && prevSort) {
            //item moved at the bottom
            newSort = prevSort + step;

        } else if (prevSort && nextSort) {
            //item moved inside two items
            newSort = (prevSort + nextSort) / 2;

        } else if (!prevSort && !nextSort) {
            //It's the first item of this column
            newSort = step * 100; //set a big value for 1st item
        }

        $item.attr("data-sort", newSort);


        $.ajax({
            url: '<?php echo_uri("projects/save_task_sort_and_status") ?>',
            type: "POST",
            data: {id: id, sort: newSort, status_id: status, project_id: project_id},
            success: function () {
                appLoader.hide();
                $('#reload-kanban-button').trigger('click');
            }
        });

    };



    $(document).ready(function () {

        var isChrome = !!window.chrome && !!window.chrome.webstore;


        $(".kanban-item-list").each(function (index) {
            var id = this.id;

            var options = {
                animation: 150,
                group: "kanban-item-list",
                onAdd: function (e) {
                    //moved to another column. update bothe sort and status
                    saveStatusAndSort($(e.item), $(e.item).closest(".kanban-item-list").attr("data-status_id"));
                },
                onUpdate: function (e) {
                    //updated sort
                    saveStatusAndSort($(e.item));
                }
            };

            //apply only on chrome because this feature is not working perfectly in other browsers.
            if (isChrome) {
                options.setData = function (dataTransfer, dragEl) {
                    var img = document.createElement("img");
                    img.src = $("#move-icon").attr("src");
                    img.style.opacity = 1;
                    dataTransfer.setDragImage(img, 5, 10);
                };

                options.ghostClass = "kanban-sortable-ghost";
                options.chosenClass = "kanban-sortable-chosen";
            }

            Sortable.create($("#" + id)[0], options);
        });


        adjustViewHeightWidth();

        var duration = 1000 * 60 * 10; // 10 minutes

        $('#reload-kanban-button').on('click', function() {
            clearInterval(interval);
        })

        var interval = setInterval(function() {
            if (!$('#ajaxModal').hasClass('in')) {
                $('#reload-kanban-button').trigger('click');
            }
        }, duration);

    });

    $(window).resize(function () {
        adjustViewHeightWidth();
    });


</script>
