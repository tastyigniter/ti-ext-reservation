This extension to the TastyIgniter built-in reservation management provides a simple booking form to accept table reservations.

### Admin Panel
In the admin user interface you can:
- Define time interval (time slot)
- Define stay time (reservation length)
- Manage your tables (units & capacity)

Go to **Restaurants > Locations > Edit Location**, under the **Orders & Reservations** tab 

### Components
| Name     | Page variable                | Description                                      |
| -------- | ---------------------------- | ------------------------------------------------ |
| Booking | `<?= component('booking') ?>` | Display the booking form              |

### Booking Component

**Properties**

| Property                 | Description              | Example Value | Default Value |
| ------------------------ | ------------------------ | ------------- | ------------- |
| mode      | Enable or disable booking     |       TRUE           |        TRUE   |
| maxGuestSize      | The maximum guest size        |       20           |      20   |
| timePickerInterval        | The interval to use for the time picker       |       30           |      30   |
| timeSlotsInterval     | The interval to use for the time slots        |       15           |      15   |
| dateFormat        | Date format to use for the date picker        |       M d, yyyy           |       M d, yyyy   |
| timeFormat        | Time format to use for the time dropdown      |       h:i a           |      h:i a   |
| dateTimeFormat        | Date time format to use for the book summary      |       l, F j, Y \\a\\t h:i a           |      l, F j, Y \\a\\t h:i a   |
| showLocationThumb     | Show Location Image Thumbnail     |       FALSE           |      FALSE   |
| locationThumbWidth        | Location thumb Height        |        95           |      95    |
| locationThumbHeight       | Location thumb Width     |        80           |      80    |
| bookingPage       | Booking page name      |      reservation/reservation           |     reservation/reservation  |
| successPage       | Page name to redirect to when checkout is successful       |      reservation/success           |     reservation/success  |

**Variables available in templates**

| Variable                  | Description                                                  |
| ------------------------- | ------------------------------------------------------------ |
| $bookingDateFormat | Date format                                                |
| $bookingTimeFormat | Time format                                               |
| $bookingDateTimeFormat | Date time format                                                |
| $bookingLocation | Instance of the current location model                                              |
| $showLocationThumb | Display location thumbnail                                                |
| $locationThumbWidth | Location thumbnail width                                                |
| $locationThumbHeight | Location thumbnail height                                               |
| $customer | Instance of the logged user model                                                |

**Example:**

```
---
title: 'Reservation'
permalink: /reservation

'[booking]': { }
---
...
<?= component('booking'); ?>
...
```

### License
[The MIT License (MIT)](https://tastyigniter.com/licence/)