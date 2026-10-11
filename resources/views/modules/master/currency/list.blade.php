@section('js','currency')
@section('page-title', __('Currencies'))
<x-app-layout>
    <main class="gmail-content bg-white d-flex">
        @include('includes.master-navigation')
        <section class="flex-grow-1 px-4 d-flex flex-column">
            @include('includes.master-page-title')
            <div class="border bdr-r-10 pt-0 pb-3 overflow-hidden">
                <!-- Table with scroll -->
                <div class="flex-grow-1 overflow-auto" style="min-height:320px;">
                    <table class="table align-middle dataTable" id="dataTable">
                        <thead class="table-light sticky-top bg-white">
                        <tr>
                            <th>#</th>
                            <th>{{ __('Code') }}</th>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Country') }}</th>
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
