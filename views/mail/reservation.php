subject = "{site_name} reservation confirmation - {reservation_number}"
==

Thank you for your reservation!

Hi, {first_name} {last_name}

Your reservation {reservation_number} at {location_name} has been booked for {reservation_guest_no} person(s) on {reservation_date} at {reservation_time}.

Thanks for reserving with us online!

==

<!-- HEADER -->
<table class="head-wrap" bgcolor="#D7D7DE">
    <tr>
        <td></td>
        <td class="header container">
            <div class="content">
                <table bgcolor="#D7D7DE">
                    <tr>
                        <td><img src="{site_logo}"/></td>
                        <td align="right"><h6 class="collapse">{site_name}</h6></td>
                    </tr>
                </table>
            </div>
        </td>
        <td></td>
    </tr>
</table><!-- /HEADER -->
<!-- BODY -->
<table class="body-wrap">
    <tr>
        <td></td>
        <td class="container" bgcolor="#FFFFFF">
            <div class="content">
                <table>
                    <tr>
                        <td>
                            <h3>Thank you for your reservation!</h3>
                            <p class="lead">Hi, {first_name} {last_name}</p>
                            <p>Your reservation {reservation_number} at {location_name} has been booked for {reservation_guest_no} person(s) on {reservation_date} at {reservation_time}.</p>
                            <p>Thanks for reserving with us online!</p>
                        </td>
                    </tr>
                </table>
            </div><!-- /content -->
        </td>
        <td></td>
    </tr>
</table><!-- /BODY -->
<!-- FOOTER -->
<table class="footer-wrap">
    <tr>
        <td></td>
        <td class="container">
            <!-- content -->
            <div class="content">
                <table>
                    <tr>
                        <td align="center">
                            <p>
                                2018 © {site_name} All Rights Reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </div><!-- /content -->
        </td>
        <td></td>
    </tr>
</table><!-- /FOOTER -->
