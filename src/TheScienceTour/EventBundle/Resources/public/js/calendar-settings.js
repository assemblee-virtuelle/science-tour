$(function () {
		var date = new Date();
		var d = date.getDate();
		var m = date.getMonth();
		var y = date.getFullYear();
		var localOptions = {
				buttonText: {
					today: "Aujourd'hui",
					month: 'Mois',
					day: 'Jour',
					week: 'Semaine'
				},
				monthNames: ['Janvier','F\u00e9vrier','Mars','Avril','Mai','Juin','Juillet','Ao\u00fbt','Septembre','Octobre','Novembre','D\u00e9cembre'],
				monthNamesShort: ['Jan','Fev','Mar','Avr','Mai','Juin','Juil','Aout','Sep','Oct','Nov','Dec'],
				dayNames: ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'],
				dayNamesShort: ['D','L','Ma','Me','J','V','S']
		}
		
		$('#calendar-holder').fullCalendar($.extend({
			theme: true,
			aspectRatio: 1.50,
			header: {
				left: 'prev',
				center: 'title',
				right: 'next'
			},
			lazyFetching:true,
            timeFormat: {
                    // for agendaWeek and agendaDay
                    agenda: 'h:mmt', // 5:00 - 6:30

                    // for all other views
                    '': 'h:mmt'            // 7p
            },
			eventSources: [
                    {
                        url: Routing.generate('fullcalendar_loader'), 
						type: 'POST',
                        error: function() {
                           alert('There was an error while fetching Events!');
                        }
                    }
			],
            eventColor: 'rgba(247, 105, 100, 0)',
            firstDay: 1
		}, localOptions));
});
