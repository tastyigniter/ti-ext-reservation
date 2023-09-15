<button
    type="button"
    class="btn btn-light text-danger"
    data-bs-toggle="modal"
    data-bs-target="#cancelReservationModal{{ $customerReservation->reservation_id }}"
>@lang('igniter.reservation::default.reservations.button_cancel')</button>
<div
    class="modal fade"
    id="cancelReservationModal{{ $customerReservation->reservation_id }}"
    aria-labelledby="cancelReservationModalLabel{{ $customerReservation->reservation_id }}"
    aria-hidden="true"
>
    <div class="modal-dialog">
        <form method="POST" data-request="{{ $__SELF__.'::onCancel' }}">
            <input type="hidden" name="reservationId" value="{{ $customerReservation->reservation_id }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5"
                        id="cancelReservationModalLabel{{ $customerReservation->reservation_id }}"
                    >@lang('igniter.reservation::default.reservations.text_title_cancel')</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <textarea
                        class="form-control"
                        name="cancel_reason"
                        id="cancelOrderReason"
                        rows="3"
                        placeholder="@lang('igniter.reservation::default.reservations.label_cancel_reason')"
                    ></textarea>
                </div>
                <div class="modal-footer">
                    <button
                        type="submit"
                        class="btn btn-primary"
                        data-attach-loading
                    >@lang('igniter.reservation::default.reservations.button_cancel')</button>
                </div>
            </div>
        </form>
    </div>
</div>
