---
title: "Reservation Extension"
section: "extensions"
sortOrder: 90
---

## Introduction

This extension to the TastyIgniter built-in reservation management provides a simple booking form to accept table
reservations.

## Installation

To install this extension, click on the **Add to Site** button on the TastyIgniter marketplace item page or search
for **Igniter.Reservation** in **Admin System > Updates > Browse Extensions**

## Admin Panel

In the admin user interface you can:

- Define time interval (time slot)
- Define stay time (reservation length)
- Manage your tables (units & capacity)

Go to **Restaurants > Locations > Edit Location**, under the **Orders & Reservations** tab

## Automations Events

- New Reservation Event
- Reservation Status Update Event
- Reservation Assigned Event

## Automations Conditions

- Reservation Attributes
- Reservation Status Attributes

## Notifications

- Reservation confirmation notification
- Reservation status update notification
- Reservation assigned notification

## Events

The Booking Manager used with this extension will fire some global events that can be useful for interacting with other
extensions.

| Event | Description | Parameters |
| ----- | ----------- | ---------- |
| `igniter.reservation.beforeSaveReservation` |    When a reservation is being created.    |           |
| `igniter.reservation.confirmed` |      When a reservation has been placed.       |      The `CartItem` instance     |

**Example of hooking an event**

```
Event::listen('igniter.reservation.confirmed', function($cartItem) {
    // ...
});
```
