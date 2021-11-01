@if ($customerReservation)
    @if ($showReviews && !empty($reviewable))
        <div class="mb-3">
            @partial('localReview::form')
        </div>
    @endif

    @partial($__SELF__.'::preview')
@else
    @partial('@list')
@endif
