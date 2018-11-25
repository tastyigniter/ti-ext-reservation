+function ($) {
    "use strict"

    var Booking = function (element, options) {
        this.$el = $(element)
        this.options = options || {}
        this.picker = null

        this.init()
        this.bindPicker()
    }

    Booking.prototype.init = function () {
    }

    Booking.prototype.bindPicker = function () {
        var $pickerEl = this.$el.find('[data-control="booking-datepicker"]'),
            options = $.extend({}, Booking.PICKER_DEFAULTS, $pickerEl.data()),
            $pickerValEl = $(options.valueElement)

        this.picker = $pickerEl.datepicker(options)

        $pickerEl.datepicker('update', new Date($pickerValEl.val()))

        this.picker.on('changeDate', function (e) {
            $pickerValEl.val(e.format('yyyy-mm-dd'))
        })
    }

    Booking.PICKER_DEFAULTS = {
        autoclose: true,
        format: 'dd-mm-yyyy',
        valueElement: '[name="date"]',
        todayHighlight: true,
        templates: {
            leftArrow: '<i class="fa fa-long-arrow-left"></i>',
            rightArrow: '<i class="fa fa-long-arrow-right"></i>'
        }
    }

    Booking.DEFAULTS = {
    }

    // PLUGIN DEFINITION
    // ============================

    var old = $.fn.booking

    $.fn.booking = function (option) {
        var args = arguments

        return this.each(function () {
            var $this = $(this)
            var data = $this.data('ti.booking')
            var options = $.extend({}, Booking.DEFAULTS, $this.data(), typeof option == 'object' && option)
            if (!data) $this.data('ti.booking', (data = new Booking(this, options)))
            if (typeof option == 'string') data[option].apply(data, args)
        })
    }

    $.fn.booking.Constructor = Booking

    $.fn.booking.noConflict = function () {
        $.fn.booking = old
        return this
    }

    $(document).render(function () {
        $('[data-control="booking"]').booking()
    })

}(window.jQuery)
