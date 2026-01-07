<div class="modal fade" id="detrackModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detrack</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div id="detrackLoading" class="text-center py-4 d-none">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="mt-2 small text-muted">Searching for available vehicles / drivers…</div>
                </div>

                <div class="row g-3" id="detrackContent">
                    <table class="table table-bordered align-middle mb-0">
                        <thead>
                            <tr class="text-center">
                                <th style="width: 30%;">Order Number</th>
                                <th style="width: 70%;">Notes</th>
                            </tr>
                        </thead>
                        <tbody id="detrackTableBody"></tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary" id="saveDetrackBtn">Save</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="driverInfoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-3 shadow">
            
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Driver Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" id="driverInfoModalBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="mt-2 small text-muted">Loading driver information...</div>
                </div>
            </div>

        </div>
    </div>
</div>
