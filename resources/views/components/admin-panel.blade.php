<div class="main-right-content">
    <div class="right-content-inner">
            {{-- Summary --}}
            <div class="statistics">
                <div class="row">
                    <div class="col-lg-3 pb-3">
                        <div class="card">
                            <div class="icon">
                                <i class="bi bi-people"></i>
                            </div>
                            <div class="num">{{ $totalOrders }}</div>
                            <div class="label">Orders</div>
                        </div>
                    </div>

                    <div class="col-lg-3 pb-3">
                        <div class="card">
                            <div class="icon">
                                <i class="bi bi-chat-left-text"></i>
                            </div>
                            <div class="num">29</div>
                            <div class="label">Pending Delivered</div>
                        </div>
                    </div>

                    <div class="col-lg-3 pb-3">
                        <div class="card">
                            <div class="icon">
                                <i class="bi bi-diamond"></i>
                            </div>
                            <div class="num">259</div>
                            <div class="label">Orders Delivered</div>
                        </div>
                    </div>

                    <div class="col-lg-3 pb-3">
                        <div class="card">
                            <div class="icon">
                                <i class="bi bi-currency-dollar"></i>
                            </div>
                            <div class="num">RM4,960</div>
                            <div class="label">Sales</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                {{-- Datepicker --}}
                @include('admin.partials.datepicker', ['orders' => $orders])
                 {{-- Table List for Mobile View --}}
                <div class="table-wrapper">
                    <div class="table-responsive dataTables_custom">
                        <table class="table-listing table user-table">
                            <thead>
                                <tr>
                                    <th>Order Number</th>
                                    <th>Source</th>
                                    <th class="date-column">Delivery Date</th>
                                    <th>Delivery Time</th>
                                    <th>Products</th>
                                    <th>City</th>
                                    <th>Postal Code</th>
                                    <th>Delivery Partners</th>
                                    <th>Status</th>
                                    <th>PL</th>
                                    <th>MC</th>
                                    <th>DO</th>
                                </tr>
                            </thead>
                            <tbody id="order-table-body">
                                @include('admin.partials.order-table', ['orders' => $orders])
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        const table = $('table.table-listing').DataTable({
            "paging": true,
            "ordering": true,
            "info": true,
            "searching": true,
            "language": {
                "search": "",
                "searchPlaceholder": "Search..."
            }
        });

        const dateColumnIndex = $('th.date-column').index(); 
        const fp = flatpickr("#custom-date-range", {
            mode: "range",
            dateFormat: "Y-m-d",
            onChange: function(selectedDates, dateStr, instance) {
                if (selectedDates.length === 2) {
                    const startDate = moment(selectedDates[0]).format('YYYY-MM-DD');
                    const endDate = moment(selectedDates[1]).format('YYYY-MM-DD');
                    applyDateFilter(startDate, endDate);
                } else {
                    if (selectedDates.length === 1) {
                        const date = moment(selectedDates[0]).format('YYYY-MM-DD');
                        applyDateFilter(date, date);
                    }
                }
            }
        });

        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                const minDate = $('#min-date').val();
                const maxDate = $('#max-date').val();
                
                if (!minDate && !maxDate) {
                    return true;
                }
                
                const rowDate = moment(data[dateColumnIndex], 'YYYY-MM-DD');                
                const startDateMoment = moment(minDate, 'YYYY-MM-DD');
                const endDateMoment = moment(maxDate, 'YYYY-MM-DD');

                if (rowDate.isBetween(startDateMoment, endDateMoment, 'days', '[]')) {
                    return true;
                }

                return false;
            }
        );

        $('#date-range-select').on('change', function() {
            const selectedRange = $(this).val();
            let startDate = null;
            let endDate = null;

            $('#custom-date-fields').hide();

            switch (selectedRange) {
                case 'all':
                    break;
                case 'today':
                    startDate = moment().format('YYYY-MM-DD');
                    endDate = moment().format('YYYY-MM-DD');
                    break;
                case 'yesterday':
                    startDate = moment().subtract(1, 'days').format('YYYY-MM-DD');
                    endDate = moment().subtract(1, 'days').format('YYYY-MM-DD');
                    break;
                case 'last7days':
                    startDate = moment().subtract(6, 'days').format('YYYY-MM-DD');
                    endDate = moment().format('YYYY-MM-DD');
                    break;
                case 'last30days':
                    startDate = moment().subtract(29, 'days').format('YYYY-MM-DD');
                    endDate = moment().format('YYYY-MM-DD');
                    break;
                case 'thismonth':
                    startDate = moment().startOf('month').format('YYYY-MM-DD');
                    endDate = moment().endOf('month').format('YYYY-MM-DD');
                    break;
                case 'lastmonth':
                    startDate = moment().subtract(1, 'month').startOf('month').format('YYYY-MM-DD');
                    endDate = moment().subtract(1, 'month').endOf('month').format('YYYY-MM-DD');
                    break;
                case 'custom':
                    $('#custom-date-fields').show();
                    return;
            }            
            applyDateFilter(startDate, endDate);
        });

        function applyDateFilter(startDate, endDate) {
            $('#min-date').val(startDate);
            $('#max-date').val(endDate);
            table.draw();
        }
    });
</script>