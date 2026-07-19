@section('js','period_closing')
@section('page-title','Period Closing')
<x-app-layout>
    <main class="gmail-content bg-white d-flex">
        @include('includes.master-navigation')
        <section class="flex-grow-1 px-4 d-flex flex-column">
            @include('includes.master-header')
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="align-items-center flex-shrink-0">
                    <p class="text-muted small mb-0">
                        Closing a period locks every transaction dated on or before its closing date across the app.
                    </p>
                </div>
                <div class="d-flex align-items-center">
                    <button class="btn btn-primary rounded-pill px-4" id="new">New Period</button>
                </div>
            </div>
            <div class="shadow bdr-r-10 py-3 flex-grow-1">
                <!-- Table with scroll -->
                <div class="flex-grow-1 overflow-auto" style="min-height:320px;">
                    <table class="table align-middle dataTable" id="dataTable" data-model-size="md">
                        <thead class="table-light sticky-top bg-white">
                        <tr>
                            <th>#</th>
                            <th>Year</th>
                            <th>Closing Date</th>
                            <th>Notes</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</x-app-layout>
