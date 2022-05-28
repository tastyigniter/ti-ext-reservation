@if ($customerReservation)
    @if ($showReviews && !empty($reviewable))
        <div class="mb-3">
            @themePartial('localReview::form')
        </div>
    @endif

    @themePartial($__SELF__.'::preview')
@else
    @themePartial('@list')
@endif
