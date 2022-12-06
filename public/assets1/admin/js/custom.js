function thousands_separators(num)
{
  var num_parts = num.toString().split(".");
  num_parts[0] = num_parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
  return num_parts.join(".");
}

function get_yymmdd_format(d)
{
  const yef = new Intl.DateTimeFormat('en', { year: 'numeric' }).format(d);
  const mof = new Intl.DateTimeFormat('en', { month: '2-digit' }).format(d);
  const daf = new Intl.DateTimeFormat('en', { day: '2-digit' }).format(d);
  return `${yef}-${mof}-${daf}`;
}

function get_date_value(v){
  var d = new Date();
  if(v != ''){
      d = new Date(v);
  }
  return d;
}

function get_date_string(v){
  var d = get_date_value(v);
  return get_yymmdd_format(d);
}

(function($) {
		"use strict";
		
	$(document).ready(function() {
		
		$("body").delegate(".datepicker", "focusin", function(){
			//$(this).datepicker();
			$(this).datepicker({
				//defaultDate: new Date(),
				changeMonth: true,
				changeYear: true,
			});
			
			/*
			var dates123 =  $( "#consumer-money-collection-date1,#payment-to-company-date,#payment-to-merchant-date" ).datepicker({
				defaultDate: "+1w",
				changeMonth: true,
				changeYear: true,
				//container: "#modal1",
				//minDate: dateToday,
				// onSelect: function(selectedDate) {
				// var option = this.id == "from" ? "minDate" : "maxDate",
				//   instance = $(this).data("datepicker"),
				//   date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat, selectedDate, instance.settings);
				//   dates.not(this).datepicker("option", option, date);
				// }
			});
			*/
		});


// Drop Down Section

    $('.dropdown-toggle-1').on('click', function(){
       $(this).parent().siblings().find('.dropdown-menu').hide();
       $(this).next('.dropdown-menu').toggle(); 
    });

  $(document).on('click', function(e) 
  {
      var container = $(".dropdown-toggle-1");

      // if the target of the click isn't the container nor a descendant of the container
      if (!container.is(e.target) && container.has(e.target).length === 0) 
      {
          container.next('.dropdown-menu').hide();
      }
  });

  });

// Drop Down Section Ends 

		// Side Bar Area Js
		$('#sidebarCollapse').on('click', function() {
			$('#sidebar').toggleClass('active');
		});
		Waves.init();
		Waves.attach('.wave-effect', ['waves-button']);
		Waves.attach('.wave-effect-float', ['waves-button', 'waves-float']);
		$('.slimescroll-id').slimScroll({
			height: 'auto'
		});
		$("#sidebar a").each(function() {
		  var pageUrl = window.location.href.split(/[?#]/)[0];
			if (this.href == pageUrl) {
				$(this).addClass("active");
				$(this).parent().addClass("active"); // add active to li of the current link
				$(this).parent().parent().prev().addClass("active"); // add active class to an anchor
				$(this).parent().parent().prev().click(); // click the item to make it drop
			}
		});

    // Side Bar Area Js Ends

    // Nice Select Active js
    $('.select').niceSelect();
    //  Nice Select Ends    
})(jQuery);


  

