<div id="activity-feed-scroll-container" class="pt-0">
    @php
        // Initialize $activities as an empty collection if it's not defined
        if (!isset($activities)) {
            $activities = collect();
        }
    @endphp

    {{-- Initial 50 activities are rendered here --}}
    <div id="activity-feed-content">
        @include('activity.feed_load_more', ['activities' => $activities])
    </div>

    @empty($activities)
        <div id="no-more-activities" class="alert alert-info text-center mt-5" role="alert">
            No recent activity to display.
        </div>
    @else
        {{-- Loader/Message for Scrolling --}}
        <div id="feed-loader" class="text-center my-3" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
        <div id="end-of-feed" class="text-center text-muted small my-3" style="display: none;">
            End of activity log.
        </div>
    @endempty

</div>

{{-- 💡 INFINITE SCROLL JAVASCRIPT --}}
<script>
    $(document).ready(function() {
        if (typeof jQuery === 'undefined') {
            console.error('jQuery not loaded. Infinite scroll will not function.');
            return;
        }

        // --- Element and State Definitions ---
        // Offcanvas root element (used for shown/hidden events)
        const offcanvasElement = document.getElementById('moduleFeedViewDrawer');
        // Scrollable element (Targeted via ID)
        const $container = $('#activity-feed-scroll-container');

        const $content = $('#activity-feed-content');
        const $loader = $('#feed-loader');
        const $endOfFeed = $('#end-of-feed');

        let offset = {{ $nextOffset ?? 10 }};
        let isLoading = false;
        let limit = {{ $limit ?? 10 }};
        let hasMore = {{ isset($activities) && $activities->flatten()->count() === ($limit ?? 10) ? 'true' : 'false' }};
        const loadUrl = '{{ route('activities.load_more') }}';

        // Function to load the next batch of activities (Logic remains unchanged)
        function loadNextBatch() {
            if (isLoading || !hasMore) {
                return;
            }

            isLoading = true;
            $loader.show();

            $.ajax({
                url: loadUrl,
                method: 'GET',
                data: { offset: offset },
                success: function(response) {
                    if (response.html && response.html.length > 0) {
                        $content.append(response.html);
                        offset = response.nextOffset;
                        hasMore = response.hasMore;

                        // After appending, check if content still doesn't fill the screen
                        // This handles cases where data is sparse
                        const container = $container[0];
                        if (hasMore && container.scrollHeight <= container.clientHeight) {
                            console.log("Content short after load. Loading next immediately.");
                            loadNextBatch();
                        }

                    } else {
                        hasMore = false;
                    }
                },
                error: function() {
                    console.error('Failed to load more activities.');
                },
                complete: function() {
                    isLoading = false;
                    $loader.hide();
                    if (!hasMore) {
                        $endOfFeed.show();
                    }
                }
            });
        }

        // Attach the scroll handler to the scrollable container
        if (offcanvasElement) {
            // First, ensure the container is properly initialized when offcanvas is shown
            $(offcanvasElement).on('shown.bs.offcanvas', function () {
                console.log('Offcanvas shown. Activating scroll handler on inner container.');

                // Initial check: Load data if the first batch doesn't fill the screen.
                const container = $container[0];
                if (hasMore && container.scrollHeight <= container.clientHeight) {
                    console.log("Container short. Loading first batch immediately.");
                    loadNextBatch();
                }
            });

            // Bind the scroll event directly to the container (not dependent on offcanvas shown event)
            // This ensures the scroll handler is always active
            $container.on('scroll', function() {
                const container = $container[0];

                // Standard, reliable calculation: (scroll position + visible height) >= (total height - tolerance)
                if (container.scrollTop + container.clientHeight >= container.scrollHeight - 50) {
                    console.log("Scroll near bottom. Loading next batch.");
                    loadNextBatch();
                }
            });
        }

        // If less than 50 loaded initially (meaning no more data), show end of feed message
        if (!hasMore) {
            $endOfFeed.show();
        }
    });
</script>
