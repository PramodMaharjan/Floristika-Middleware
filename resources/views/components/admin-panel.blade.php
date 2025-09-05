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

                        <div class="graph mt-2">
                         <img src="{{ asset('images/graph.png') }}" class="img-fluid" alt="Graph 1" />
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 pb-3">
                    <div class="card">
                        <div class="icon">
                            <i class="bi bi-chat-left-text"></i>
                        </div>
                        <div class="num">29</div>
                        <div class="label">Pending Delivered</div>

                        <div class="graph mt-2">
                            <img src="{{ asset('images/graph1.png') }}" class="img-fluid" alt="Graph 2" />
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 pb-3">
                    <div class="card">
                        <div class="icon">
                            <i class="bi bi-diamond"></i>
                        </div>
                        <div class="num">259</div>
                        <div class="label">Orders Delivered</div>

                        <div class="graph mt-2">
                            <img src="{{ asset('images/graph2.png') }}" class="img-fluid" alt="Graph 3" />
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 pb-3">
                    <div class="card">
                        <div class="icon">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                        <div class="num">RM4,960</div>
                        <div class="label">Sales</div>

                        <div class="graph mt-2">
                            <img src="{{ asset('images/graph3.png') }}" class="img-fluid" alt="Graph 4" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- Table List for Mobile View --}}
        <div class="table-wrapper">
        <div class="table-responsive">
            <table class="table-listing table user-table">
                <thead>
                    <tr>
                        <th>Order Number</th>
                        <th>Source</th>
                        <th>Delivery Date</th>
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
                <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $order->source }}</td>
                            <td>{{ $order->delivery_date }}</td>
                            <td>{{ $order->delivery_time }}</td>
                            <td>{{ $order->products }}</td>
                            <td>{{ $order->city }}</td>
                            <td>{{ $order->postal_code }}</td>
                            <td>{{ $order->delivery_partners }}</td>
                            <td>{{ $order->status }}</td>
                            <td>{{ $order->pl }}</td>
                            <td>{{ $order->mc }}</td>
                            <td>{{ $order->do }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Table List for Mobile View -->
    {{-- <div class="table-small">
        <div class="table-each">
            <div class="table-each-inner p-3">
                <p><b>Order Number:</b> info@gmail.com</p>
                <p><b>Source:</b> Kathmandu, Nepal</p>
                <p><b>Delivery Date:</b> Kathmandu, Nepal</p>
                <p><b>Delivery Time:</b> Kathmandu, Nepal</p>
                <p><b>Products:</b> Kathmandu, Nepal</p>
                <p><b>City:</b> Kathmandu, Nepal</p>
                <p><b>Postal Code:</b> Kathmandu, Nepal</p>
                <p><b>Delivery Partners:</b> Kathmandu, Nepal</p>
                <p><b>Status</b> Kathmandu, Nepal</p>
                <p><b>PL</b> Kathmandu, Nepal</p>
                <p><b>MC</b> Kathmandu, Nepal</p>
                <p><b>DO</b> Kathmandu, Nepal</p>
            </div>
        </div>

    </div> --}}
    {{-- Pagination --}}
    <nav aria-label="Page navigation example" class="d-flex justify-content-end">
        <ul class="pagination">

            <li class="page-item"><a class="page-link active" href="#">1</a></li>
            <li class="page-item"><a class="page-link" href="#">2</a></li>
            <li class="page-item"><a class="page-link" href="#">3</a></li>
            <li class="page-item">
                <a class="page-link" href="#" aria-label="Next">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>
    </div>
</div>