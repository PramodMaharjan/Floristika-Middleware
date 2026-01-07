<div class="main-right-content">
    <div class="right-content-inner">
            {{-- Summary --}}
            <div class="statistics">
                <div class="row">
                    <div class="col-6 col-lg-3 pb-3 d-flex">
                        <div class="card flex-fill">
                            <div class="icon">
                                <i class="bi bi-cart"></i>
                            </div>
                            <div class="label">Borders</div>
                            {{-- <div class="num" id="total-orders">{{ number_format($totalOrders) }}</div>
                            <div class="label mt-2 sale" id="total-sales">RM {{ number_format($totalSales, 2) }}</div> --}}
                            <div class="num" id="total-orders"></div>
                            <div class="label mt-2 sale" id="total-sales"></div>
                        </div>
                    </div>

                    <div class="col-6 col-lg-3 pb-3 d-flex">
                        <div class="card flex-fill">
                            <div class="icon">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <div class="label">Pending delivery</div>
                            {{-- <div class="num" id="pending-delivered">{{ number_format($unfulfilledOrders) }}</div>
                            <div class="label mt-2 sale" id="pending-sales">RM {{ number_format($unfulfilledSales, 2) }}</div> --}}
                            <div class="num" id="pending-delivered"></div>
                            <div class="label mt-2 sale" id="pending-sales"></div>
                        </div>
                    </div>

                    <div class="col-6 col-lg-3 pb-3 d-flex">
                        <div class="card flex-fill">
                            <div class="icon">
                                <i class="bi bi-bag-check"></i>
                            </div>
                            <div class="label">Orders delivered</div>
                            {{-- <div class="num" id="orders-delivered">{{ number_format($fulfilledOrders) }}</div>
                            <div class="label mt-2 sale" id="delivered-sales">RM {{ number_format($fulfilledSales, 2) }}</div> --}}
                            <div class="num" id="orders-delivered"></div>
                            <div class="label mt-2 sale" id="delivered-sales"></div>
                        </div>
                    </div>

                    <div class="col-6 col-lg-3 pb-3 d-flex">
                        <div class="card flex-fill">
                            <div class="icon">
                                <i class="bi bi-currency-dollar"></i>
                            </div>
                            <div class="label">Sales {{ now()->format('F') }}</div>
                            {{-- <div class="num" id="current-month-sales">RM {{ number_format($currentMonthSales, 2) }}</div> --}}
                            <div class="num" id="current-month-sales"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container-fluid">
                <div class="d-flex flex-wrap align-items-center gap-2 orders-toolbar mb-3">
                    @include('admin.partials.datepicker', ['orders' => $orders])
                    @include('admin.partials.action')
                    @include('admin.partials.bulk-print')
                    @include('admin.partials.purchase-list')
                    @include('admin.partials.third-party-logistics')
                </div>
            </div>

            {{-- Table List --}}
            <div class="table-wrapper position-relative">
                <div class="table-overlay-loader d-none">
                    <div class="spinner-border text-secondary"></div>
                </div>
                <div class="table-responsive dataTables_custom">
                    <table id="order-table" class="table-listing table user-table">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="select-all-orders">
                                </th>
                                <th>Order Number</th>
                                <th>TikTok Order Number</th>
                                <th>Source</th>
                                <th>Delivery Partners</th>
                                <th class="delivery-date">Delivery Date</th>
                                <th>Delivery Time</th>
                                <th>Products</th>
                                <th>City</th>
                                <th>SubZone</th>
                                <th>Zone</th>
                                <th>Postal Code</th>
                                <th>Status</th>
                                <th>Shipment Status</th>
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
                <div id="table-loader" class="text-center my-3">
                    <div class="spinner-border text-secondary" role="status">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
