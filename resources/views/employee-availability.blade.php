@extends('layouts.main')

@section('title')
    Employee Availibility List
@stop


@section('content')
    <div class="col-lg-3">
        <table class="table table-sm">
            <tbody>
            <tr>
                <td>
                    <button class="btn btn-success btn-xs mr-1" disabled><span class="fas fa-check-circle" title="Mark Reviewed"/></button>


                </td>
                <td>Click to mark employee availability as COMPLETE and entered into Kronos.</td>
            </tr>
            <tr>
                <td>
                    <button class="btn btn-warning btn-xs mr-1" disabled><span class="fas fa-minus-circle" title="Mark Pending"/></button>
                </td>
                <td>Click to mark employee availability back to a PENDING state.</td>
            </tr>
            <tr>
                <td>
                    <button class="btn btn-danger btn-xs mr-1" disabled><span class="fas fa-times-circle" title="Mark Removed"/></button>
                </td>
                <td>Click to mark employee availability as REMOVED.</td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <div class="form-row">
                    <div class="col form-inline"><h2 class="sub-header">WG&E Employee Availibility List</h2>
                    </div>
                </div>
            </div>
            {{--}}
            <div class="card-header">
                <h2 class="sub-header">WG&E Employee Overtime List</h2>
            </div>
            {{--}}
            <div class="card-body p-0">
                <table class="table table-sm table-bordered table-hover" id="employee-availability-table">
                    <thead>
                    <tr>
                        <th style="width: 5%">Number</th>
                        <th style="width: 13%">Accepted</th>
                        <th style="width: 10%">Unavailable</th>
                        <th style="width: 10%">Refused</th>
                        <th style="width: 10%">Review Status</th>
                        <th style="width: 5%">Updated At</th>
                        <th style="width: 5%">Created At</th>
                        <th style="width: 5%">Action</th>
                    </tr>
                    </thead>
                    <tfoot>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
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



@stop

@push('scripts')

    <script type="application/javascript">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var  otTable = $('#employee-availability-table').DataTable({
            lengthMenu: [[10, 20, 50, 100, 200], [10, 20, 50, 100, 200]],
            processing: true,
            language: {
                processing: '<i class="fas fa-spinner fa-1x fa-spin"></i>'
            },
            serverSide: false,
            colReorder: true,
            responsive: true,
            order: [[5, 'asc'],[6, 'asc']],
            dom: '<"top"Bl>tr<"bottom"ip>',
            buttons: [
                'csv',
                'excel',
                'pdf',
                {
                    text: 'Reload',
                    action: function (){
                        refresh();
                    }
                }],
            ajax: {
                url: '{{ url('overtimelist/employee-availability/dt') }}',
                type: "POST",
            },
            columns: [
                {data: 'employee_id', name: 'employee_id'},
                {data: 'accepted', name: 'accepted'},
                {data: 'unavailable', name: 'unavailable'},
                {data: 'refused', name: 'refused'},
                {data: 'review_status', name: 'review_status'},
                {data: 'updated_at', name: 'updated_at'},
                {data: 'created_at', name: 'created_at'},
                {data: 'action', name: 'action'}
            ],
            initComplete: function () {
                this.api().columns([4]).every(function () {
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
                this.api().columns([0,5,6]).every(function () {
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

        function refresh(){
                otTable.ajax.reload();
        }

        function completeReviewStatus(reviewId) {
            var completeStatus = {
                'id': reviewId,
                'review_status': 'COMPLETE'
            }
            updateReviewStatus(completeStatus)
        }

        function pendingReviewStatus(reviewId) {
            var pendingStatus = {
                'id': reviewId,
                'review_status': 'PENDING'
            }
            updateReviewStatus(pendingStatus)
        }

        function removeReviewStatus(reviewId) {
            var removeStatus = {
                'id': reviewId,
                'review_status': 'REMOVED'
            }
            updateReviewStatus(removeStatus)
        }

        function updateReviewStatus(employeeAvailabilityStatusData) {
            $.ajax({
                type: 'POST',
                url: '{{ url('overtimelist/employee-availability/status') }}',
                data: employeeAvailabilityStatusData,
                success: function (data) {
                    $('#success').text(data).fadeIn(1).fadeOut(2000);
                    refresh();
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
