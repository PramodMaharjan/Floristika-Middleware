<div class="modal fade" id="editDeliveryModal" tabindex="-1" aria-labelledby="editDeliveryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editDeliveryModalLabel">Edit Delivery Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editDeliveryForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="deliveryDate" class="form-label">Delivery Date</label>
                        <input type="date" class="form-control" id="deliveryDate" name="deliveryDate">
                    </div>
                    <div class="mb-3">
                        @php
                            $savedTimeSlot = $order->delivery_time ?? null;
                            $timeSlots = [
                                '09:00 am - 01:00 pm' => '9am - 1pm',
                                '02:00 pm - 06:00 pm' => '2pm - 6pm',
                                '07:00 pm - 10:00 pm' => '7pm - 10pm',
                                '10:00 am - 06:00 pm' => '10am - 6pm',
                            ];
                        @endphp
                        <label for="timeSlot" class="form-label">Time Slot</label>
                        <select class="form-select" id="timeSlot" name="timeSlot">
                            <option value="" disabled {{ !$savedTimeSlot ? 'selected' : '' }}>
                                Select a time slot
                            </option>
                            @foreach ($timeSlots as $value => $displaySlot)
                                @php
                                    $isSelected = $savedTimeSlot === $value;
                                @endphp
                                <option value="{{ $value }}" {{ $isSelected ? 'selected' : '' }}>
                                    {{ $displaySlot }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>