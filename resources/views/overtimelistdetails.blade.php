@extends('layouts.main')

@section('title')
    Overtime List Details
@stop


@section('content')
    <div class="col-lg-12">
        <div class="row">
            <div class="col-lg-3">
                <table class="table table-sm table-striped table-hover">
                    <tbody>
                    <tr>
                        <td>Number:</td>
                        <td>Employee Number</td>
                    </tr>
                    <tr>
                        <td>R:</td>
                        <td>Regular Hours</td>
                    </tr>
                    <tr>
                        <td>S:</td>
                        <td>Standby Hours</td>
                    </tr>
                    <tr>
                        <td>SC:</td>
                        <td>Standby Additional Covid Hours</td>
                    </tr>
                    <tr>
                        <td>OT:</td>
                        <td>Overtime Hours</td>
                    </tr>
                    <tr>
                        <td>OTR:</td>
                        <td>Overtime Red Hours</td>
                    </tr>
                    <tr>
                        <td>OTP:</td>
                        <td>Overtime Premium Hours</td>
                    </tr>
                    <tr>
                        <td>OTPR:</td>
                        <td>Overtime Premium Red Hours</td>
                    </tr>
                    <tr>
                        <td>DT:</td>
                        <td>Doubletime</td>
                    </tr>
                    <tr>
                        <td>DTR:</td>
                        <td>Doubletime Red Hours</td>
                    </tr>
                    <tr>
                        <td>DTP:</td>
                        <td>Doubletime Premium Hours</td>
                    </tr>
                    <tr>
                        <td>DTPR:</td>
                        <td>Doubletime Premium Red Hours</td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-lg-9">
                <table class="table table-sm table-striped table-hover">
                    <tbody>
                    <tr>
                        <td>OT->R:</td>
                        <td>Converting Overtime Hours to Regular Hours (OT * 1.5)</td>
                    </tr>
                    <tr>
                        <td>OTR->R:</td>
                        <td>Converting Overtime Red Hours to Regular Hours (OTR * 1.5)</td>
                    </tr>
                    <tr>
                        <td>OTP->R:</td>
                        <td>Converting Overtime Premium Hours to Regular Hours (OTP * 1.75)</td>
                    </tr>
                    <tr>
                        <td>OTPR->R:</td>
                        <td>Converting Overtime Premium Red hours to Regular Hours (OTPR * 1.75)</td>
                    </tr>
                    <tr>
                        <td>DT->R:</td>
                        <td>Converting Doubletime Hours to Regular Hours (DT * 2)</td>
                    </tr>
                    <tr>
                        <td>DTR->R:</td>
                        <td>Converting Doubletime Red Hours to Regular Hours (DTR * 2)</td>
                    </tr>
                    <tr>
                        <td>DTP->R:</td>
                        <td>Converting Doubletime Premium Hours to Regular Hours (DTP * 2.25)</td>
                    </tr>
                    <tr>
                        <td>DTPR->R:</td>
                        <td>Converting Doubletime Premium Red Hours to Regular Hours (DTPR * 2.25)</td>
                    </tr>
                    <tr>
                        <td>=H:</td>
                        <td>Adding all calculated hours together. Excluding current Regular Hours (S + OT->R +
                            OTR->R +
                            OTP->R + OTPR->R + DT->R + DTR->R + DTP->R + DTPR->R)
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-12 pt-1">
        <div class="card">
            <div class="card-header">
                <div class="form-row">
                    <div class="col form-inline"><h2 class="sub-header">WG&E Employee Overtime List Details</h2>
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
                        <th style="width: 4%">R</th>
                        <th style="width: 4%">S</th>
                        <th style="width: 4%">SC</th>
                        <th style="width: 4%">OT</th>
                        <th style="width: 4%">OTR</th>
                        <th style="width: 4%">OTP</th>
                        <th style="width: 4%">OTPR</th>
                        <th style="width: 4%">DT</th>
                        <th style="width: 4%">DTR</th>
                        <th style="width: 4%">DTP</th>
                        <th style="width: 4%">DTPR</th>
                        <th style="width: 5%">OT->R</th>
                        <th style="width: 5%">OTR->R</th>
                        <th style="width: 5%">OTP->R</th>
                        <th style="width: 5%">OTPR->R</th>
                        <th style="width: 5%">DT->R</th>
                        <th style="width: 5%">DTR->R</th>
                        <th style="width: 5%">DTP->R</th>
                        <th style="width: 5%">DTPR->R</th>
                        <th style="width: 4%">=H</th>
                    </tr>
                    </thead>

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
            order: [[19, 'asc']],
            dom: '<"top"Bfl>tr<"bottom"ip>',
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
                url: '{{ url('overtimelistdetails/dt') }}',
                type: "POST",
                data: function (d) {
                    d.startDate = $('input[name=start_date]').val();
                    d.endDate = $('input[name=end_date]').val();
                }
            },
            columns: [
                {data: 'employee_number', name: 'employee_number'},
                {data: 'regular', name: 'regular'},
                {data: 'standby', name: 'standby'},
                {data: 'standby_additional_covid', name: 'standby_additional_covid'},
                {data: 'overtime', name: 'overtime'},
                {data: 'overtime_red', name: 'overtime_red'},
                {data: 'overtime_premium', name: 'overtime_premium'},
                {data: 'overtime_premium_red', name: 'overtime_premium_red'},
                {data: 'doubletime', name: 'doubletime'},
                {data: 'doubletime_red', name: 'doubletime_red'},
                {data: 'doubletime_premium', name: 'doubletime_premium'},
                {data: 'doubletime_premium_red', name: 'doubletime_premium_red'},
                {data: 'overtime_to_regular', name: 'overtime_to_regular'},
                {data: 'overtime_red_to_regular', name: 'overtime_red_to_regular'},
                {data: 'overtime_premium_to_regular', name: 'overtime_premium_to_regular'},
                {data: 'overtime_premium_red_to_regular', name: 'overtime_premium_red_to_regular'},
                {data: 'doubletime_to_regular', name: 'doubletime_to_regular'},
                {data: 'doubletime_red_To_regular', name: 'doubletime_red_To_regular'},
                {data: 'doubletime_premium_to_regular', name: 'doubletime_premium_to_regular'},
                {data: 'doubletime_premium_red_to_regular', name: 'doubletime_premium_red_to_regular'},
                {data: 'overtime_equalized', name: 'overtime_equalized'}

            ],
            initComplete: function () {
                this.api().columns([]).every(function () {
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
                this.api().columns([]).every(function () {
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

    </script>
@endpush
