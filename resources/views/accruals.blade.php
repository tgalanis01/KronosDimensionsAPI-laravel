@extends('layouts.master')

@section('title')
    Accruals
@stop


@section('content')

    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h2 class="sub-header">Accruals</h2>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-bordered table-hover" id="accruals-table">
                    <thead>
                    <tr>
                        <th style="width: 10%">Number</th>
                        <th style="width: 10%">Name</th>
                        <th style="width: 5%">Payrule</th>
                        <th style="width: 10%">Labor Category</th>
                        <th style="width: 10%">Primary Job</th>
                        <th style="width: 10%">Primary Org</th>
                        <th style="width: 10%">Type</th>
                        <th style="width: 5%">Hours</th>
                        <th style="width: 10%">AccrualCode</th>
                        <th style="width: 10%">Date</th>
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
        $('#accruals-table').DataTable({
            lengthMenu: [[10, 20, 50, 100, 200], [10, 20, 50, 100, 200]],
            processing: true,
            serverSide: false,
            colReorder: true,
            responsive: true,
            scrollCollapse: true,
            dom: '<"top"Bl>t<"bottom"ip>',
            buttons: ['csv'],
            ajax: '{{ url('accruals/dt', ['$employeeNumber' => Auth::user()->employee_number]) }}',
            columns: [
                {data: 'employeeNumber', name: 'employeeNumber'},
                {data: 'employeeName', name: 'employeeName'},
                {data: 'payrule', name: 'payrule'},
                {data: 'laborCategory', name: 'laborCategory'},
                {data: 'primaryJob', name: 'primaryJob'},
                {data: 'primaryOrg', name: 'primaryOrg'},
                {data: 'transactionType', name: 'transactionType'},
                {data: 'transactionHours', name: 'transactionHours'},
                {data: 'transactionAccrualCode', name: 'transactionAccrualCode'},
                {data: 'transactionDate', name: 'transactionDate'}
            ],
            initComplete: function () {
                this.api().columns([1, 3, 4, 5, 6, 8]).every(function () {
                    var column = this;
                    var select = $('<select style="width: 100%"><option value=""></option></select>')
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
                this.api().columns([0]).every(function () {
                    var column = this;
                    var input = document.createElement("input");
                    // start - this is the code inserted by me
                    $(input).attr('style', 'width: 100%');
                    // end  - this is the code inserted by me
                    $(input).appendTo($(column.footer()).empty())
                        .on('change', function () {
                            column.search($(this).val(), false, false, true).draw();
                        });
                });
            }
        });
    </script>
@endpush
