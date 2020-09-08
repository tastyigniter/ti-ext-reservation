+function ($) {
    "use strict"

    var Booking = function (element, options) {
        this.$el = $(element)
        this.options = options || {}
        this.$picker = null
        this.$dataLocker = null

        this.init()
    }

    Booking.prototype.init = function () {
	    if (this.$picker = this.$el.find('[data-control="datepicker"]'))
            this.initDatepicker();	    
    }
    
    Booking.prototype.initDatepicker = function () {
        this.$picker.datepicker({
	        daysOfWeekDisabled: this.options.datepickerDisableddaysofweek,
	        datesDisabled: this.options.datepickerDisableddates,
            format: 'yyyy-mm-dd',
            icons: {
                time: "fa fa-clock-o",
                date: "fa fa-calendar",
                up: "fa fa-arrow-up",
                down: "fa fa-arrow-down"
            },
            startDate: this.options.datepickerStartdate,//'08-09-2020',
            todayHighlight: true,
        });
        
        this.$dataLocker = this.$picker.next('input');
        this.$picker.on('changeDate', $.proxy(this.onSelectDatePicker, this))	    
    }
    
    Booking.prototype.onSelectDatePicker = function(event) {
        var pickerDate = moment(event.date.toDateString())
        var lockerValue = pickerDate.format('YYYY-MM-DD')

        this.$dataLocker.val(lockerValue);
        
		jQuery.ajax(location.pathname + '?picker_step=' + (parseInt(this.$el.find('[name="picker_step"]').val())-1) + '&location=' + this.$el.find('[name="location"]').val() + '&date=' + lockerValue, {
	         dataType: 'html'
	     })
	     .done(function (html) {
	         html = jQuery.parseHTML(html);
	         html.forEach(function (node) {
	             if (node.tagName && node.tagName.toUpperCase() == 'MAIN') {
	                 var newEl, currentEl;
	                 if ((newEl = node.querySelector('#ti-datepicker-options')) && (currentEl = document.querySelector('#ti-datepicker-options'))) {
	                     currentEl.innerHTML = newEl.innerHTML;
	                 }
	             }
	         });
	     });        
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
