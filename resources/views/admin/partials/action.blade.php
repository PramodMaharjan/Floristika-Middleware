    <div class="flex-shrink-0 flex-grow-1 flex-lg-grow-0" style="min-width: 160px;">
      <div class="dropdown w-100">
    <button class="btn btn-primary dropdown-toggle w-100" type="button" id="actionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        Select Action
    </button>
    <ul class="dropdown-menu w-100" aria-labelledby="actionsDropdown">
        {{-- <li><a class="dropdown-item" href="#">Create Invoice</a></li>
        <li><a class="dropdown-item" href="#">Mark as Processing</a></li> --}}
        <li><a class="dropdown-item order-action" data-status="On the way">Mark as On the way</a></li>
        <li><a class="dropdown-item order-action" data-status="Fulfilled">Mark as Delivered</a></li>
        <li><a class="dropdown-item order-action" data-status="Returned">Mark as Returned</a></li>
        <li><a class="dropdown-item order-action" data-status="Seen">Mark as Seen</a></li>
        </ul>
      </div>
</div>
