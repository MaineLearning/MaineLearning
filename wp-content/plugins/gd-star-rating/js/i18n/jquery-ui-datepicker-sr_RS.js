/* Serbian i18n for the jQuery UI date picker plugin. */
/* Written by Milan Petrovic. */
jQuery(function($){
	$.datepicker.regional['sr_RS'] = {
		clearText: 'izbriši', clearStatus: 'Izbriši trenutni datum',
		closeText: 'Zatvori', closeStatus: 'Zatvori kalendar',
		prevText: '&#x3c;', prevStatus: 'Prikaži prethodni mesec',
		prevBigText: '&#x3c;&#x3c;', prevBigStatus: '',
		nextText: '&#x3e;', nextStatus: 'Prikaži sledeći mesec',
		nextBigText: '&#x3e;&#x3e;', nextBigStatus: '',
		currentText: 'Danas', currentStatus: 'Današnji datum',
		monthNames: ['Januar','Februar','Mart','April','Maj','Jun',
		'Jul','Avgust','Septembar','Oktobar','Novembar','Decembar'],
		monthNamesShort: ['Jan','Feb','Mar','Apr','Maj','Jun',
		'Jul','Avg','Sep','Okt','Nov','Dec'],
		monthStatus: 'Prikaži mesece', yearStatus: 'Prikaži godine',
		weekHeader: 'Ned', weekStatus: 'Nedelja',
		dayNames: ['Nedelja','Ponedeljak','Utorak','Sreda','Četvrtak','Petak','Subota'],
		dayNamesShort: ['Ned','Pon','Uto','Sre','Čet','Pet','Sub'],
		dayNamesMin: ['Ne','Po','Ut','Sr','Če','Pe','Su'],
		dayStatus: 'Odaberi DD za prvi dan nedelje', dateStatus: '\'Datum\' D, M d',
		dateFormat: 'dd.mm.yy.', firstDay: 1,
		initStatus: 'Odaberi datum', isRTL: false};
	$.datepicker.setDefaults($.datepicker.regional['sr_RS']);
});