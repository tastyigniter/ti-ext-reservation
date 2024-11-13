<?php

return [
    'text_tab_general' => 'General',
    'text_component_title' => 'Booking Component',
    'text_component_desc' => 'Displays the booking form',
    'text_tab_reservation' => 'Reservation options',

    'text_heading' => 'Reserve A Table',
    'text_success_heading' => 'Reservation Confirmation',
    'text_time_heading' => 'Select Time',
    'text_reservation' => 'My Reservation',
    'text_heading_success' => 'Reservation Confirmed',
    'text_no_table' => 'No tables available at your local restaurant.',
    'text_find_msg' => 'Please use the form below to find a table to reserve',
    'text_time_msg' => 'Available reservation time slots on %s for %s guests',
    'text_no_time_slot' => '<span class="text-danger">No reservation time slot available, please go back to check your table details.</span>',
    'text_location_closed' => 'Sorry, but we\'re closed, come back during opening hours',
    'text_date_format' => '%D, %M %j, %Y',
    'text_person' => 'person',
    'text_people' => 'people',
    'text_mail_reservation' => 'Reservation email to customer',
    'text_mail_reservation_alert' => 'Reservation alert to admin',

    'text_subject' => 'Table Reserved - %s!',
    'text_greetings' => 'Thank You %s,',
    'text_success_message' => 'Your reservation at %s has been booked for %s on %s.<br />Thanks for reserving with us online!',

    'label_status' => 'Status',
    'label_location' => 'Location',
    'label_guest_num' => 'Number of guests',
    'label_date' => 'Date',
    'label_time' => 'Time',
    'label_occasion' => 'Occasion',
    'label_select' => '- please select -',

    'label_first_name' => 'First Name',
    'label_last_name' => 'Last Name',
    'label_email' => 'Email Address',
    'label_confirm_email' => 'Confirm Email Address',
    'label_telephone' => 'Telephone',
    'label_comment' => 'Special Requests',

    'label_offer_reservation' => 'Offer Reservations',
    'label_reservation_time_interval' => 'Reservation Time Interval',
    'label_reservation_stay_time' => 'Reservation Stay Time',
    'label_min_reservation_advance_time' => 'Min. Advance Reservation Time',
    'label_max_reservation_advance_time' => 'Max. Advance Reservation Time',
    'label_min_reservation_guest_num' => 'Min. Reservation Guest Size',
    'label_max_reservation_guest_num' => 'Max. Reservation Guest Size',
    'label_reservation_include_start_time' => 'Include Start Time in Reservation Timeslots',
    'label_auto_allocate_table' => 'Automatically Assign Tables To Reservations',
    'label_reservation_cancellation_timeout' => 'Reservation Cancellation Timeout',
    'label_limit_guests' => 'Limit Reservation Guests Count',
    'label_limit_guests_count' => 'Maximum Guests Per Interval',

    'help_reservation_include_start_time' => 'Disabling will start the reservation timeslots from the scheduled open time plus stay time.',
    'help_reservation_time_interval' => 'Set the number of minutes between each reservation time',
    'help_reservation_stay_time' => 'Set in minutes the average time a guest will stay at a table',
    'help_min_reservation_advance_time' => 'Set the minimum number of days required before a guest can book a table.',
    'help_max_reservation_advance_time' => 'Set the maximum number of days that a guest can book a table.',
    'help_min_reservation_guest_num' => 'Set the minimum number of guests required to book a table.',
    'help_max_reservation_guest_num' => 'Set the maximum number of guests allowed to book a table.',
    'help_reservation_cancellation_timeout' => 'Set when a customer can no longer cancel a booking. Number of minutes before booking time. Leave as 0, to disable customer booking cancellation.',
    'help_limit_guests_count' => 'Set the maximum number of guests allowed per time slot',

    'button_find_table' => 'Find Table',
    'button_select_time' => 'Select Time',
    'button_change' => 'Change details',
    'button_reset' => 'Reset',

    'button_find_again' => 'Find Table Again',
    'button_reservation' => 'Complete Reservation',

    'error_invalid_location' => 'Selected location is invalid.',
    'error_invalid_date' => 'Date must be after today, you can only make future reservations!',
    'error_invalid_datetime' => 'Selected reservation date time is invalid',
    'error_invalid_time' => 'Time must be between restaurant opening time!',
    'error_telephone_invalid' => 'Invalid number',
    'error_telephone_invalid_country_code' => 'Invalid country code',
    'error_telephone_too_short' => 'Too short',
    'error_telephone_too_long' => 'Too long',

    'alert_reservation_disabled' => 'Table reservation has been disabled, please contact administrator.',
    'alert_no_table_available' => 'No table found for the specified number of guests at the selected location.',
    'alert_fully_booked' => 'We are fully booked for the selected date and time, please select a different date or time.',

    'activity_reservation_created_title' => 'New reservation.',
    'activity_reservation_created' => '<b>:properties.full_name</b> created a reservation.',

    'reservations' => [
        'component_title' => 'Account Reservations Component',
        'component_desc' => 'Displays and manages account reservations',
        'text_heading' => 'Recent Reservations',
        'text_my_account' => 'My Account',
        'text_view_heading' => 'My Reservation View',
        'text_empty' => 'There are no reservation(s).',
        'text_title_cancel' => 'Cancel Order',

        'column_id' => 'Reservation ID',
        'column_status' => 'Status',
        'column_location' => 'Location',
        'column_date' => 'Time - Date',
        'column_table' => 'Table Name',
        'column_guest' => 'Guest Number',
        'column_occasion' => 'Occasion',
        'column_telephone' => 'Telephone',
        'column_comment' => 'Comment',

        'label_cancel_reason' => 'Reason for cancellation',

        'button_reserve' => 'Make Reservation',
        'button_cancel' => 'Cancel Reservation',
        'button_back' => 'Back',
        'btn_view' => 'View',

        'alert_cancel_success' => 'Reservation successfully canceled.',
        'alert_cancel_failed' => 'Unable to cancel reservation, please contact us.',
    ],
];
