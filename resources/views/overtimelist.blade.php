@extends('layouts.main')

@section('title')
    Overtime List
@stop


@section('content')

    <div class="col-lg-3">
        <table class="table table-sm">
            <tbody>
            <tr>
                <td>
                    <button class="btn btn-success btn-xs mr-1" disabled><span class="fas fa-calendar-plus" title="Mark Accepted"/>
                    </button>
                </td>
                <td>Click to mark employee as accepted.</td>
            </tr>
            <tr>
                <td>
                    <button class="btn btn-warning btn-xs mr-1" disabled><span class="fas fa-calendar-minus" title="Mark Unavailable"/>
                    </button>
                </td>
                <td>Click to mark employee as unavailable.</td>
            </tr>
            <tr>
                <td>
                    <button class="btn btn-danger btn-xs mr-1"  disabled><span class="fas fa-calendar-times" title="Mark Refused"/>
                    </button>
                </td>
                <td>Click to mark employee as refused.</td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <div class="form-row">
                    <div class="col form-inline"><h2 class="sub-header">WG&E Employee Overtime List</h2>
                        <div class="form-row">
                            <label class="col-form-label-lg ml-3" for="start_date">Start Date:</label>
                            <input type="date" class="form-control ml-3" name="start_date" id="start_date"
                                   value="{{ \Carbon\Carbon::now()->startOfYear()->toDateString() }}">
                            <label class="col-form-label-lg ml-3" for="end_date">End Date:</label>
                            <input type="date" class="form-control ml-3" name="end_date" id="end_date"
                                   value="{{ \Carbon\Carbon::now()->startOfWeek()->addDay('-1')->toDateString() }}">
                        </div>
                    </div>
                </div>
            </div>
            {{--}}
            <div class="card-header">
                <h2 class="sub-header">WG&E Employee Overtime List</h2>
            </div>
            {{--}}
            <div class="card-body p-0">
                <table class="table table-sm table-bordered table-hover" id="overtimelist-table">
                    <thead>
                    <tr>
                        <th style="width: 5%">Number</th>
                        <th style="width: 13%">Name</th>
                        {{--}}
                        <th style="width: 13%">Labor Category</th>
                        {{--}}
                        <th style="width: 10%">Phone</th>
                        {{--}}
                        <th style="width: 10%">Department</th>
                        <th style="width: 2%">Company</th>
                        <th style="width: 8%">Division</th>
                        <th style="width: 8%">Department</th>
                        {{--}}
                        <th style="width: 10%">Job Category</th>
                        <th style="width: 10%">Job Title</th>
                        <th style="width: 5%">Hours</th>
                        <th style="width: 5%">Not Available</th>
                        <th style="width: 5%">Refused</th>
                        <th style="width: 5%">Action</th>
                    </tr>
                    </thead>
                    <tfoot>
                    <tr>
                        <td></td>
                        <td></td>
                        {{--}}
                        <td></td>
                        {{0--}}
                        <td></td>
                        <td></td>
                        {{--}}
                        <td></td>
                        <td></td>
                        <td></td>
                        {{--}}
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>


    <div class="modal fade" id="unavailable" tabindex="-1" role="dialog" aria-labelledby="unavailableLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="unavailableLabel">Mark Unavailable: <span
                            id="employee_number_header"></span></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="form-group">
                            <label for="message-text" class="col-form-label">Start Time:</label>
                            <input type="datetime-local" class="form-control" id="unavailable_from">
                        </div>
                        <div class="form-group">
                            <label for="message-text" class="col-form-label">End Time:</label>
                            <input type="datetime-local" class="form-control" id="unavailable_to">
                        </div>
                        <div class="form-group">
                            <label for="message-text" class="col-form-label">Hours:</label>
                            <input type="number" class="form-control" id="hours_unavailable">
                        </div>
                        <div class="form-group">
                            <label for="recipient-name" class="col-form-label">Outage #:</label>
                            <input type="text" class="form-control" id="outage_ticket_number">
                        </div>
                        <div class="form-group">
                            <label for="message-text" class="col-form-label">Work Order #:</label>
                            <input type="text" class="form-control" id="work_order_ticket_number">
                        </div>
                        <div class="form-group">
                            <label for="message-text" class="col-form-label">Service Order #:</label>
                            <input type="text" class="form-control" id="service_order_ticket_number">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="submit_unavailable">Submit</button>
                </div>
            </div>
        </div>
    </div>
@stop

@push('scripts')

    <script type="application/javascript">
        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        })

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#start_date').change(function () {
            refresh();
        });
        $('#end_date').change(function () {
            refresh();
        });

        var otTable = $('#overtimelist-table').DataTable({
            lengthMenu: [[10, 20, 50, 100, 200], [10, 20, 50, 100, 200]],
            processing: true,
            language: {
                processing: '<i class="fas fa-spinner fa-1x fa-spin"></i>'
            },
            serverSide: false,
            colReorder: true,
            responsive: true,
            order: [[4, 'asc'], [5, 'asc']],
            dom: '<"top"Bl>tr<"bottom"ip>',
            buttons: [
                'csv',
                'excel',
                'pdf',
                {
                    text: 'Reload',
                    action: function () {
                        refresh();
                    }
                }],
            ajax: {
                url: '{{ url('overtimelist/dt') }}',
                type: "POST",
                data: function (d) {
                    d.startDate = $('input[name=start_date]').val();
                    d.endDate = $('input[name=end_date]').val();
                }
            },
            columns: [
                {data: 'employee_number', name: 'employee_number'},
                {data: 'full_name', name: 'full_name'},
                    {{--}}
                    {data: 'labor_category', name: 'labor_category'},
                    {{--}}
                {
                    data: 'phone', name: 'phone'
                },
                    {{--}}
                    {data: 'department', name: 'department'},

                        {data: 'company', name: 'company'},
                        {data: 'division', name: 'division'},
                        {{--}}
                {
                    data: 'job_category', name: 'job_category'
                },
                {data: 'primary_job', name: 'primary_job'},
                {data: 'overtime_equalized', name: 'overtime_equalized'},
                {data: 'red_time_hours', name: 'red_time_hours'},
                {data: 'unavailable_hours', name: 'unavailable_hours'},
                {data: 'action', name: 'action'}

            ],
            initComplete: function () {
                this.api().columns([3, 4, 5]).every(function () {
                    var column = this;
                    var select = $('<select><option value=""></option></select>')
                        .appendTo($(column.footer()).empty())
                        .on('change', function () {
                            var val = $.fn.dataTable.util.escapeRegex(
                                $(this).val()
                            );
                            column
                                .search(val ? '^' + val + '$' : '', true, false)
                                .draw();
                        });
                    column.data().unique().sort().each(function (d, j) {
                        select.append('<option value="' + d + '">' + d + '</option>')
                    });

                });
                this.api().columns([0, 1, 2]).every(function () {
                    var column = this;
                    var input = document.createElement("input");
                    // start - this is the code inserted by me
                    $(input).attr('style', 'width: 100%');
                    // end  - this is the code inserted by me
                    $(input).appendTo($(column.footer()).empty())
                        .on('change', function () {
                            column.search($(this).val(), true, false, true, true).draw();
                        });
                });
            }
        });
        $('#overtimelist-table_processing').hide();

        function refresh() {
            var startDate = $('input[name=start_date]').val();
            var endDate = $('input[name=end_date]').val();
            if (startDate && endDate) {
                otTable.ajax.reload();
            }
        }


        function acceptedOvertime(employeeNumber) {
            var acceptedOvertimeData = {
                'employee_id': employeeNumber,
                'accepted': 1,
                'unavailable': 0,
                'refused': 0
            }
            console.log(acceptedOvertimeData);
            updateEmployeeOvertime(acceptedOvertimeData)
        }

        function unavailableOvertime(employeeNumber) {
            var unavailableOvertimeData = {
                'employee_id': employeeNumber,
                'accepted': 0,
                'unavailable': 1,
                'refused': 0
            }
            console.log(unavailableOvertimeData);
            updateEmployeeOvertime(unavailableOvertimeData)
        }

        function refusedOvertime(employeeNumber) {
            var refusedOvertimeData = {
                'employee_id': employeeNumber,
                'accepted': 0,
                'unavailable': 0,
                'refused': 1
            }
            console.log(refusedOvertimeData);

            updateEmployeeOvertime(refusedOvertimeData)
        }

        function updateEmployeeOvertime(overtimeData) {
            $.ajax({
                type: 'POST',
                url: '{{ url('overtimelist/employee-availability') }}',
                data: overtimeData,
                success: function (data) {
                    $('#success').text(data).fadeIn(1).fadeOut(2000);
                    console.log(data);
                    // $('.item' + $('.id').text()).remove();
                },
                statusCode: {
                    500: function () {
                        $('#error').text(" Unknown error please contact support").fadeIn(1).fadeOut(2000);
                    },
                    422: function (data) {
                        //
                    }
                }
            });
        }
    </script>
@endpush
