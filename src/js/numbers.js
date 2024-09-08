
// noinspection JSUnusedGlobalSymbols

/**
 * Calculates missing data for a comission or discount: net_amount = gross_amount - gross_amount*percentage.
 * 
 * @param {string|number|null|undefined} gross gross amount
 * @param {string|number|null|undefined} percentage percentage commission
 * @param {string|number|null|undefined} commission commission value
 * @param {string|number|null|undefined} net net amount
 * @returns {{error_message: string, net: number, commission: number, gross: number, percentage: number, ok: boolean}}
 */
function markdown(gross, percentage, commission, net) {
    let error_message = '';
    let ok = true;
    try {
        gross = typeof gross === 'undefined' || gross === '' ? null : parseFloat(gross);
        percentage = typeof percentage === 'undefined' || percentage === '' ? null : parseFloat(percentage);
        commission = typeof commission === 'undefined' || commission === '' ? null : parseFloat(commission);
        net = typeof net === 'undefined' || net === '' ? null : parseFloat(net);
        
        if(isNaN(gross)) error_message += "gross no es númerico. ";
        if(isNaN(percentage)) error_message += "percentage no es percentage. ";
        if(isNaN(commission)) error_message += "commission no es númerico. ";
        if(isNaN(net)) error_message += "net no es númerico. ";

        if(error_message)
            return {ok: false, gross: gross, percentage: percentage, commission: commission, net: net, error_message:error_message};

        if(gross !== null) {
            if(percentage !== null) {
                commission = gross * percentage / 100.00;
                net = gross - commission;
            } else if(commission !== null) {
                percentage = gross ? commission / gross * 100.00 : 0.00;
                net = gross - commission;
            } else if(net !== null) {
                commission = gross - net;
                percentage = gross ? commission / gross * 100.00 : 0.00;
            } else {
                ok = false;
                error_message = "Faltan datos";
            }
        } else if(percentage !== null) {
            if(commission !== null) {
                gross = commission ? percentage / commission * 100.00 * 100.00 : 0.00;
                net = gross - commission;
            } else if(net !== null) {
                gross = percentage !== 1.00 ? net / (1 - percentage / 100.00) : 0.00;
                commission = gross - net;
            } else {
                ok = false;
                error_message = "Faltan datos";
            }
        } else if(commission !== null) {
            if(net !== null) {
                gross = net + commission;
                percentage = commission / gross * 100.00;
            } else {
                ok = false;
                error_message = "Faltan datos";
            }
        } else {
            ok = false;
            error_message = "Faltan datos";
        }
    } catch(err) {
        ok = false;
        error_message = err.toString();
    }
    return {ok: ok, gross: gross, percentage: percentage, commission: commission, net: net, error_message:error_message};
}
