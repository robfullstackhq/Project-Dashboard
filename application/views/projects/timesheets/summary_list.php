<div class="table-responsive">
    <table id="timesheet-summary-table" class="display" cellspacing="0" width="100%">            
    </table>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        $("#timesheet-summary-table").appTable({
            source: '<?php echo_uri("projects/timesheet_summary_list_data/"); ?>',
            filterParams: {project_id: "<?php echo $project_id; ?>"},
            filterDropdown: [
                {name: "user_id", class: "w200", options: <?php echo $project_members_dropdown; ?>},
                {name: "task_id", class: "w200", options: <?php echo $tasks_dropdown; ?>},
                {name: "group_by", class: "w200", options: <?php echo $group_by_dropdown; ?>},
            ],
            rangeDatepicker: [{startDate: {name: "start_date", value: ""}, endDate: {name: "end_date", value: ""}, showClearButton: true}],
            columns: [
                {visible: false, searchable: false},
                {title: "<?php echo lang("member"); ?>"},
                {title: "<?php echo lang("task"); ?>"},
                {visible: false, title: "Agreed Duration (Hours)", "class": "w15p"},
                {title: "<?php echo lang("duration"); ?>", "class": "w15p"},
                {title: "<?php echo lang("hours"); ?>", "class": "w15p"}
            ],
            printColumns: [0, 1, 2, 3, 4, 5],
            xlsColumns: [0, 1, 2, 3, 4, 5],
            summation: [{column: 4, dataType: 'time'}, {column: 5, dataType: 'number'}],
            onRelaodCallback: function (tableInstance, filterParams) {

                //we'll show/hide the task/member column based on the group by status

                if (filterParams && filterParams.group_by === "member") {
                    showHideAppTableColumn(tableInstance, 1, true);
                    showHideAppTableColumn(tableInstance, 2, false);
                    showHideAppTableColumn(tableInstance, 3, false);
                } else if (filterParams && filterParams.group_by === "task") {
                    showHideAppTableColumn(tableInstance, 1, false);
                    showHideAppTableColumn(tableInstance, 2, true);
                    showHideAppTableColumn(tableInstance, 3, true);
                } else {
                    showHideAppTableColumn(tableInstance, 1, true);
                    showHideAppTableColumn(tableInstance, 2, true);
                    showHideAppTableColumn(tableInstance, 3, false);
                }

                //clear this status for next time load
                clearAppTableState(tableInstance);
            }
        });
    });
</script>