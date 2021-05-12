<div>
    <div class="card">
        <div class="card-header"><i class="fa fa-align-justify"></i> Simple Table</div>
        <div class="card-body">
            <table class="table table-responsive-sm">
                <thead>
                    <tr>
                        <th>Year</th>
                        <th>Gross Pay</th>
                        <th>Allowances, Example Phone</th>
                        <th>Hourly Pay (Based on calc)</th>
                        <th>Extra Hours Worked</th>
                        <th>Total Extra Worked Year based on F</th>
                        <th>Total Gross</th>
                        <th>Net (After Tax)</th>
                        <th>Net + Extra Work (After Tax)</th>
                        <th>Diff - Current Pay & Past Pay</th>
                        <th>Diff extra work after tax</th>
                    </tr>
                </thead>
                <tbody>
                @for($i=0; $i<50; $i++)
                    <tr>
                        <td>1-15-2021</td>
                        <td>$200.00</td>
                        <td>$75.00</td>
                        <td>$0.10</td>
                        <td>32.00</td>
                        <td>384.00</td>
                        <td>$318.87</td>
                        <td>$160.00</td>
                        <td>$166.00</td>
                        <td>2.00</td>
                        <td>$6.00</td>
                        <td></td>
                    </tr>
                @endfor
                </tbody>
            </table>
            <ul class="pagination">
                <li class="page-item"><a class="page-link" href="#">Prev</a></li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item"><a class="page-link" href="#">4</a></li>
                <li class="page-item"><a class="page-link" href="#">Next</a></li>
            </ul>
        </div>
    </div>
</div>