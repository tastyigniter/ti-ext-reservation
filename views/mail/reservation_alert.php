subject = "New reservation on {site_name}"
==

You received a table reservation!

You received a table reservation from {site_name}.

Customer name: {first_name} {last_name}
Reservation no: {reservation_number}
Restaurant: {location_name}
No of guest(s): {reservation_guest_no} person(s)
Reservation date: {reservation_date}
Reservation time: {reservation_time}

==

<!-- BODY -->
<table class="body-wrap">
    <tr>
        <td></td>
        <td class="container" bgcolor="#FFFFFF">
            <div class="content">
                <table>
                    <tr>
                        <td>
                            <h3>You received a table reservation!</h3>
                            <p>You received a table reservation from {site_name}.</p>
                            <table class="contact">
                                <tr>
                                    <td width="163"><strong>Customer name</strong></td>
                                    <td width="397">{first_name} {last_name}</td>
                                </tr>
                                <tr>
                                    <td><strong>Reservation no</strong></td>
                                    <td>{reservation_number}</td>
                                </tr>
                                <tr>
                                    <td><strong>Restaurant</strong></td>
                                    <td>{location_name}</td>
                                </tr>
                                <tr>
                                    <td><strong>No of guest(s)</strong></td>
                                    <td>{reservation_guest_no} person(s)</td>
                                </tr>
                                <tr>
                                    <td><strong>Reservation date</strong></td>
                                    <td>{reservation_date}</td>
                                </tr>
                                <tr>
                                    <td><strong>Reservation time</strong></td>
                                    <td>{reservation_time}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div><!-- /content -->
        </td>
        <td></td>
    </tr>
</table><!-- /BODY -->
