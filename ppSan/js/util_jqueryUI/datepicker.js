let datePickerUtil = {
    init:function(selector, options) {
        $("#num_compra_dup_a_partir_viewdateX").datepicker({
            altField:"#num_compra_dup_a_partir",
            altFormat:"yy-mm-dd",
            autoSize: true,
            changeMonth: true,
            changeYear: true,
            constrainInput: true,
            currentText: 'Hoy',
            closeText: 'Cerrar',
            dateFormat:"yy-mm-dd",
            gotoCurrent: false,
            monthNamesShort: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            showButtonPanel: true,
            showOn: "focus",
        })
            .datepicker( "setDate", new Date($("#num_compra_dup_a_partir").val()) )
            .on('change', function(){datePickerValidDate(this);});
    },
    getYmd:function(element) {
        let $el = $(element);
        const dated = $el.datepicker("getDate");
        if(dated === null)
            return null;
        return $el.datepicker("getDate").toISOString().substring(0, 10);
    },
    getMaxDate:function(element) {
        let $el = $(element);
        const maxDate = $el.datepicker('option', 'maxDate');
        return maxDate === null ? null : $.datepicker._determineDate($el, maxDate );

    },
    getMinDate:function(element) {
        let $el = $(element);
        const minDate = $el.datepicker('option', 'minDate');
        return minDate === null ? null : $.datepicker._determineDate($el, minDate );

    },
    validate: function(element) {
        let $el = $(element);
        let val = $el.val();

        if(val.trim() === '')
            if($el[0].required) {
                $el.addClass("datePickerError");
                return 'required';
            } else {
                $el.removeClass("datePickerError");
                return 'ok';
            }

        const datePickerDate = $el.datepicker("getDate");
        let datePickerDateFormatted;
        try {
            datePickerDateFormatted = $.datepicker.formatDate($el.datepicker('option', 'dateFormat'), datePickerDate )
        } catch(e) {
            $el.removeClass("datePickerError");
            return 'ok';
        }

        if(datePickerDateFormatted !== val) {
            $el.addClass("datePickerError");
            return 'invalid';
        }

        const maxDate = $el.datepicker('option', 'maxDate');
        if( maxDate !== null && datePickerDate > $.datepicker._determineDate($el, maxDate )) {
            $el.addClass("datePickerError");
            return 'gr';
        }
        const minDate = $el.datepicker('option', 'minDate');
        if( minDate !== null && datePickerDate < $.datepicker._determineDate($el, minDate )) {
            $el.addClass("datePickerError");
            return 'lt';
        }
        $el.removeClass("datePickerError");
        return 'ok';
    }
}
Object.freeze(datePickerUtil);
