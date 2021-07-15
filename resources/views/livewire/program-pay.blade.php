<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <div>
                <h4 class="card-title mb-0">Home Loan Chart</h4>
                <div class="small text-muted">15/07/2021</div>
            </div>
            <div class="btn-toolbar d-none d-md-block" role="toolbar" aria-label="Toolbar with buttons">
                <div class="btn-group btn-group-toggle mx-3" data-toggle="buttons">
                    <label class="btn btn-outline-secondary">
                        <input id="option1" type="radio" name="options" autocomplete="off"> Day
                    </label>
                    <label class="btn btn-outline-secondary active">
                        <input id="option2" type="radio" name="options" autocomplete="off" checked=""> Month
                    </label>
                    <label class="btn btn-outline-secondary">
                        <input id="option3" type="radio" name="options" autocomplete="off"> Year
                    </label>
                </div>
                
            </div>
        </div>
        <div class="c-chart-wrapper" style="height:300px;margin-top:40px;">
            <div class="chartjs-size-monitor">
                <div class="chartjs-size-monitor-expand">
                    <div class=""></div>
                </div>
                <div class="chartjs-size-monitor-shrink">
                    <div class=""></div>
                </div>
            </div>
            <canvas class="chart chartjs-render-monitor" id="main-chart" height="375" width="588" style="display: block; height: 300px; width: 471px;"></canvas>
            <div id="main-chart-tooltip" class="c-chartjs-tooltip center" style="opacity: 0; left: 376.142px; top: 280.441px;">
                <div class="c-tooltip-header">
                    <div class="c-tooltip-header-item">T</div>
                </div>
                <div class="c-tooltip-body">
                    <div class="c-tooltip-body-item"><span class="c-tooltip-body-item-color" style="background-color: rgba(3, 9, 15, 0.1);"></span><span class="c-tooltip-body-item-label">My First dataset</span><span class="c-tooltip-body-item-value">172</span></div>
                    <div class="c-tooltip-body-item"><span class="c-tooltip-body-item-color" style="background-color: transparent;"></span><span class="c-tooltip-body-item-label">My Second dataset</span><span class="c-tooltip-body-item-value">87</span></div>
                    <div class="c-tooltip-body-item"><span class="c-tooltip-body-item-color" style="background-color: transparent;"></span><span class="c-tooltip-body-item-label">My Third dataset</span><span class="c-tooltip-body-item-value">65</span></div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer">
        <div class="row text-center">
            <div class="col-sm-12 col-md mb-sm-2 mb-0">
                <div class="text-muted">Loan Amount</div><strong>250,000</strong>
                <div class="progress progress-xs mt-2">
                    <div class="progress-bar bg-success" role="progressbar" style="width: 40%" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
            <div class="col-sm-12 col-md mb-sm-2 mb-0">
                <div class="text-muted">Annual Interest Rate</div><strong>20%</strong>
                <div class="progress progress-xs mt-2">
                    <div class="progress-bar bg-info" role="progressbar" style="width: 20%" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
            <div class="col-sm-12 col-md mb-sm-2 mb-0">
                <div class="text-muted">Loan Periods</div><strong>360 Months (payments)</strong>
                <div class="progress progress-xs mt-2">
                    <div class="progress-bar bg-warning" role="progressbar" style="width: 60%" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
            <div class="col-sm-12 col-md mb-sm-2 mb-0">
                <div class="text-muted">Extra Payments</div><strong>1200 $</strong>
                <div class="progress progress-xs mt-2">
                    <div class="progress-bar bg-danger" role="progressbar" style="width: 80%" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>
    </div>
</div>