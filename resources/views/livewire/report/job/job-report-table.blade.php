<div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>Job Number</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Activity</th>
                    <th>AWB/MBL No</th>
                    <th>HBL/HAWB No</th>
                    <th>Shipper</th>
                    <th>Consignee</th>
                    <th>POL</th>
                    <th>POD</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($jobReportData['jobs']) && count($jobReportData['jobs']) > 0)
                    @foreach($jobReportData['jobs'] as $job)
                        <tr>
                            <td>{{ $job->row_no }}</td>
                            <td>{{ $job->posted_at }}</td>
                            <td>{{ $job->customer->name ?? 'N/A' }}</td>
                            <td>{{ $job->activity->name ?? 'N/A' }}</td>
                            <td>{{ $job->awb_no }}</td>
                            <td>{{ $job->hbl_no }}</td>
                            <td>{{ $job->shipper }}</td>
                            <td>{{ $job->consignee }}</td>
                            <td>{{ $job->pol }}</td>
                            <td>{{ $job->pod }}</td>
                            <td>
                                @if($job->status == 'draft')
                                    <span class="badge bg-secondary">Draft</span>
                                @elseif($job->status == 'active')
                                    <span class="badge bg-primary">Active</span>
                                @elseif($job->status == 'completed')
                                    <span class="badge bg-success">Completed</span>
                                @elseif($job->status == 'cancelled')
                                    <span class="badge bg-danger">Cancelled</span>
                                @else
                                    <span class="badge bg-info">{{ $job->status }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="11" class="text-center">No data available</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
