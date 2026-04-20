@section('js','container_type')
@section('page-title','Container Types')
<x-app-layout>
    <main class="gmail-content bg-white d-flex">
        @include('includes.master-navigation')
        <section class="flex-grow-1 px-4 d-flex flex-column">
            @include('includes.master-header')
            <div class="shadow bdr-r-10 py-3 flex-grow-1">
                <!-- Table with scroll -->
                <div class="flex-grow-1 overflow-auto">
                    <table class="table align-middle dataTable" id="dataTable">
                        <thead class="table-light sticky-top bg-white">
                        <tr>
                            <th>#</th>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Description</th>
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
