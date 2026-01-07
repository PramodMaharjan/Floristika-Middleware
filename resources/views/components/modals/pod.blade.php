<div class="modal fade" id="podModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-sm rounded-3">
      <div class="modal-header">
        <h5 class="modal-title">Proof of Delivery</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row mb-3">
          <div class="col-5 fw-semibold">Waybill Status :</div>
          <div class="col-7" id="podStatus">{{ $order->shipment_status ?? 'Awaiting Shipment Handover' }}</div>
        </div>
        <div class="row">
          <div class="col-5 fw-semibold">Download :</div>
          <div class="col-7">
            <button class="btn btn-primary btn-sm" id="downloadPodBtn" data-waybill="{{ $order['lineclear_waybill_no'] }}">Download</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="detrackPodModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content border-0 shadow rounded-4">

      <div class="modal-header border-bottom pb-2">
        <h5 class="modal-title fw-semibold d-flex align-items-center">
          Detrack Tracking
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
    <div class="modal-body p-4">
      
        <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded-3 shadow-sm">
            <div class="d-flex align-items-center">
                <span class="bg-warning rounded-circle d-inline-block me-2" style="width:12px; height:12px;"></span>
                <span class="fw-semibold">Job Status</span>
            </div>
            <span id="detrackStatus" class="fw-bold text-secondary">{{ $order->shipment_status ?: 'Dispatched' }}</span>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded-3 shadow-sm">
            <div class="d-flex align-items-center">
                <span class="bg-success rounded-circle d-inline-block me-2" style="width:12px; height:12px;"></span>
                <span class="fw-semibold">Driver Assignment</span>
            </div>
            <span id="assignStatus" class="fw-bold text-secondary">
                {{ !empty($order['detrack_assigned_to']) ? 'Assigned' : 'Unassigned' }}
            </span>
        </div>

        <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded-3 shadow-sm">
            <div class="d-flex align-items-center">
                <i class="bi bi-file-earmark-arrow-down fs-5 text-success me-2"></i>
                <span class="fw-semibold">Download POD</span>
            </div>
            <button class="btn btn-success btn-sm d-flex align-items-center" 
                    id="downloadDetrackPodBtn" 
                    data-job-id="{{ $order['detrack_job_id'] ?? '' }}">
                <i class="bi bi-download me-1"></i> Download
            </button>
        </div>

    </div>
    </div>
  </div>
</div>





