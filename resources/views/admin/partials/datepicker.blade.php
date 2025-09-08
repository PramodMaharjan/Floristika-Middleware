<div class="col-md-3 mb-3">
    <div class="input-group">
        <span class="input-group-text">
            <i class="fa-solid fa-calendar-days"></i>
        </span>
        <select id="date-range-select" class="form-select">
            <option value="all">All</option>
            <option value="today">Today</option>
            <option value="yesterday">Yesterday</option>
            <option value="last7days">Last 7 Days</option>
            <option value="last30days">Last 30 Days</option>
            <option value="thismonth">This Month</option>
            <option value="lastmonth">Last Month</option>
            <option value="custom">Custom Range</option>
        </select>
    </div>
</div>
<div class="col-md-3 mb-3" id="custom-date-fields" style="display: none;">
    <div class="input-group">
        <span class="input-group-text">
            <i class="fa-solid fa-calendar-days"></i>
        </span>
        <input type="text" id="custom-date-range" class="form-control" placeholder="Select dates">
        <input type="hidden" id="min-date">
        <input type="hidden" id="max-date">
    </div>
</div>

