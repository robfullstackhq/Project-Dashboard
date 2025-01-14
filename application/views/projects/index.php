-<div id="page-content" class="p20 clearfix">
    <div class="panel panel-default">
        <div class="page-title clearfix">
            <h1><?php echo lang('projects'); ?></h1>
            <div class="title-button-group">
                <?php
                if ($can_create_projects) {
                    echo modal_anchor(get_uri("projects/modal_form"), "<i class='fa fa-plus-circle'></i> " . lang('add_project'), array("class" => "btn btn-default", "title" => lang('add_project')));
                }
                ?>
            </div>
        </div>
        <div class="table-responsive">
            <table id="project-table" class="display" cellspacing="0" width="100%">
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        $('#confirmationModal').on('show.bs.modal', function() {
            $(this).find('.alert').remove()
        })

        var optionVisibility = <?php echo ($can_edit_projects || $can_delete_projects == 1 ? 'true' : 'false'); ?>;
        var adminVisibility = <?php echo ($this->login_user->is_admin == 1 ? 'true' : 'false'); ?>;

        $("#project-table").appTable({
            source: '<?php echo_uri("projects/list_data") ?>',
            stateSave: false,
            multiSelect: [
                {
                    name: "status",
                    text: "<?php echo lang('status'); ?>",
                    options: [
                        {text: '<?php echo lang("open") ?>', value: "open", isChecked: true},
                        {text: '<?php echo lang("completed") ?>', value: "completed"},
                        {text: 'Invoiced', value: "invoiced"},
                        {text: '<?php echo lang("hold") ?>', value: "hold"},
                        {text: '<?php echo lang("canceled") ?>', value: "canceled"}
                    ]
                }
            ],
            filterDropdown: [{name: "project_label", class: "w200", options: <?php echo $project_labels_dropdown; ?>}],
            singleDatepicker: [{name: "deadline", defaultText: "<?php echo lang('deadline') ?>",
                    options: [
                        {value: "expired", text: "<?php echo lang('expired') ?>"},
                        {value: moment().format("YYYY-MM-DD"), text: "<?php echo lang('today') ?>"},
                        {value: moment().add(1, 'days').format("YYYY-MM-DD"), text: "<?php echo lang('tomorrow') ?>"},
                        {value: moment().add(7, 'days').format("YYYY-MM-DD"), text: "<?php echo sprintf(lang('in_number_of_days'), 7); ?>"},
                        {value: moment().add(15, 'days').format("YYYY-MM-DD"), text: "<?php echo sprintf(lang('in_number_of_days'), 15); ?>"}
                    ]}],
            columns: [
                {"data": 0, title: '<?php echo lang("id") ?>', "class": "w50"},
                {"data": 1, title: '<?php echo lang("title") ?>', "class": "w300"},
                {"data": 2, title: '<?php echo lang("client") ?>', "class": "w10p"},
                {"data": 4, visible: false, searchable: false},
                // {title: '<?php echo lang("start_date") ?>', "class": "w10p", "iDataSort": 4},
                {"data": 5, title: '<?php echo lang("start_date") ?>', "iDataSort": 4},
                {"data": 6, visible: false, searchable: false},
                // {title: '<?php echo lang("deadline") ?>', "class": "w10p", "iDataSort": 6},
                {"data": 7, title: '<?php echo lang("deadline") ?>', "iDataSort": 6},
                {"data": 8, title: '<?php echo lang("progress") ?>', "class": "w10p"},
                // {title: '<?php echo lang("status") ?>', "class": "w10p"}
                {"data": 9, title: '<?php echo lang("status") ?>'}
                <?php echo $custom_field_headers; ?>,
                {"data": 3, visible: adminVisibility, title: '<?php echo lang("price") ?>', "class": "w50"},
                {visible: optionVisibility, title: '<i class="fa fa-bars"></i>', "class": "text-center option w100"}
            ],
            order: [[1, "desc"]],
            printColumns: combineCustomFieldsColumns([0, 1, 2, 3, 5, 7, 8, 9], '<?php echo $custom_field_headers; ?>'),
            xlsColumns: combineCustomFieldsColumns([0, 1, 2, 3, 5, 7, 8, 9], '<?php echo $custom_field_headers; ?>')
        });
    });
</script>
