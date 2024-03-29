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
  var d = new Date(1900,0,1);
  if(v != ''){
      d = new Date(v);
  }
  return d;
}

function get_date_string(v){
  var d = get_date_value(v);
  return get_yymmdd_format(d);
}

$(function ($) {
    "use strict";

    $(document).ready(function () {


//**************************** CUSTOM JS SECTION ****************************************

    // LOADER
      if(gs.is_loader == 1)
      {
        $(window).on("load", function (e) {
          setTimeout(function(){
              $('#preloader').fadeOut(500);
            },100)
        });
      }

    // LOADER ENDS

      //  Alert Close
      $("button.alert-close").on('click',function(){
        $(this).parent().hide();
      });


    //More Categories
    $('.rx-parent').on('click', function() {
            $('.rx-child').toggle();
            $(this).toggleClass('rx-change');
        });



    //  FORM SUBMIT SECTION

    $(document).on('submit','#contactform',function(e){
      e.preventDefault();
      $('.gocover').show();
      $('button.submit-btn').prop('disabled',true);
          $.ajax({
           method:"POST",
           url:$(this).prop('action'),
           data:new FormData(this),
           contentType: false,
           cache: false,
           processData: false,
           success:function(data)
           {
              if ((data.errors)) {
              $('.alert-success').hide();
              $('.alert-danger').show();
              $('.alert-danger ul').html('');
                for(var error in data.errors)
                {
                  $('.alert-danger ul').append('<li>'+ data.errors[error] +'</li>')
                }
                $('#contactform input[type=text], #contactform input[type=email], #contactform textarea').eq(0).focus();
                $('#contactform .refresh_code').trigger('click');

              }
              else
              {
                $('.alert-danger').hide();
                $('.alert-success').show();
                $('.alert-success p').html(data);
                $('#contactform input[type=text], #contactform input[type=email], #contactform textarea').eq(0).focus();
                $('#contactform input[type=text], #contactform input[type=email], #contactform textarea').val('');
                $('#contactform .refresh_code').trigger('click');

              }
              $('.gocover').hide();
              $('button.submit-btn').prop('disabled',false);
           }

          });

    });
    //  FORM SUBMIT SECTION ENDS


    //  SUBSCRIBE FORM SUBMIT SECTION

    $(document).on('submit','#subscribeform',function(e){
      e.preventDefault();
      $('#sub-btn').prop('disabled',true);
          $.ajax({
           method:"POST",
           url:$(this).prop('action'),
           data:new FormData(this),
           contentType: false,
           cache: false,
           processData: false,
           success:function(data)
           {
              if ((data.errors)) {

                for(var error in data.errors) {
                  toastr.error(langg.subscribe_error);
                }
              }
              else {
                 toastr.success(langg.subscribe_success);
                  $('.preload-close').click()
              }

              $('#sub-btn').prop('disabled',false);


           }

          });

    });

    //  SUBSCRIBE FORM SUBMIT SECTION ENDS


    // LOGIN FORM
    $("#loginform").on('submit', function (e) {
      var $this = $(this).parent();
      e.preventDefault();
      $this.find('button.submit-btn').prop('disabled', true);
      $this.find('.alert-info').show();
      $this.find('.alert-info p').html($('#authdata').val());
      $.ajax({
        method: "POST",
        url: $(this).prop('action'),
        data: new FormData(this),
        dataType: 'JSON',
        contentType: false,
        cache: false,
        processData: false,
        success: function (data) {
          if ((data.errors)) {
            $this.find('.alert-success').hide();
            $this.find('.alert-info').hide();
            $this.find('.alert-danger').show();
            $this.find('.alert-danger ul').html('');
            for (var error in data.errors) {
              $this.find('.alert-danger p').html(data.errors[error]);
            }
          } else {
            $this.find('.alert-info').hide();
            $this.find('.alert-danger').hide();
            $this.find('.alert-success').show();
            $this.find('.alert-success p').html('Success !');
            if (data == 1) {
              location.reload();
            } else {
              window.location = data;
            }

          }
          $this.find('button.submit-btn').prop('disabled', false);
        }

      });

    });
    // LOGIN FORM ENDS


    // MODAL LOGIN FORM
    $(".mloginform").on('submit', function (e) {
      var $this = $(this).parent();
      e.preventDefault();
      $this.find('button.submit-btn').prop('disabled', true);
      $this.find('.alert-info').show();
      var authdata = $this.find('.mauthdata').val();
      $('.signin-form .alert-info p').html(authdata);
      $.ajax({
        method: "POST",
        url: $(this).prop('action'),
        data: new FormData(this),
        dataType: 'JSON',
        contentType: false,
        cache: false,
        processData: false,
        success: function (data) {
          if ((data.errors)) {
            $this.find('.alert-success').hide();
            $this.find('.alert-info').hide();
            $this.find('.alert-danger').show();
            $this.find('.alert-danger ul').html('');
            for (var error in data.errors) {
              $('.signin-form .alert-danger p').html(data.errors[error]);
            }
          } else {
            $this.find('.alert-info').hide();
            $this.find('.alert-danger').hide();
            $this.find('.alert-success').show();
            $this.find('.alert-success p').html('Success !');
            if (data == 1) {
              location.reload();
            } else {
              window.location = data;
            }

          }
          $this.find('button.submit-btn').prop('disabled', false);
        }

      });

    });
    // MODAL LOGIN FORM ENDS

    // REGISTER FORM
    $("#registerform").on('submit', function (e) {
      var $this = $(this).parent();
      e.preventDefault();
      $this.find('button.submit-btn').prop('disabled', true);
      $this.find('.alert-info').show();
      $this.find('.alert-info p').html($('#processdata').val());
      $.ajax({
        method: "POST",
        url: $(this).prop('action'),
        data: new FormData(this),
        dataType: 'JSON',
        contentType: false,
        cache: false,
        processData: false,
        success: function (data) {

          if (data == 1) {
            window.location = mainurl + '/user/dashboard';
          } else {

            if ((data.errors)) {
              $this.find('.alert-success').hide();
              $this.find('.alert-info').hide();
              $this.find('.alert-danger').show();
              $this.find('.alert-danger ul').html('');
              for (var error in data.errors) {
                $this.find('.alert-danger p').html(data.errors[error]);
              }
              $this.find('button.submit-btn').prop('disabled', false);
            } else {
              $this.find('.alert-info').hide();
              $this.find('.alert-danger').hide();
              $this.find('.alert-success').show();
              $this.find('.alert-success p').html(data);
              $this.find('button.submit-btn').prop('disabled', false);
            }

          }
          $('.refresh_code').click();

        }

      });

    });
    // REGISTER FORM ENDS


    // MODAL REGISTER FORM
    $(".mregisterform").on('submit', function (e) {
      e.preventDefault();
      var $this = $(this).parent();
      $this.find('button.submit-btn').prop('disabled', true);
      $this.find('.alert-info').show();
      var processdata = $this.find('.mprocessdata').val();
      $this.find('.alert-info p').html(processdata);
      $.ajax({
        method: "POST",
        url: $(this).prop('action'),
        data: new FormData(this),
        dataType: 'JSON',
        contentType: false,
        cache: false,
        processData: false,
        success: function (data) {
          if (data == 1) {
            window.location = mainurl + '/user/dashboard';
          } else {

            if ((data.errors)) {
              $this.find('.alert-success').hide();
              $this.find('.alert-info').hide();
              $this.find('.alert-danger').show();
              $this.find('.alert-danger ul').html('');
              for (var error in data.errors) {
                $this.find('.alert-danger p').html(data.errors[error]);
              }
              $this.find('button.submit-btn').prop('disabled', false);
            } else {
              $this.find('.alert-info').hide();
              $this.find('.alert-danger').hide();
              $this.find('.alert-success').show();
              $this.find('.alert-success p').html(data);
              $this.find('button.submit-btn').prop('disabled', false);
            }
          }

          $('.refresh_code').click();

        }
      });

    });
    // MODAL REGISTER FORM ENDS


    // FORGOT FORM

    $("#forgotform").on('submit', function (e) {
      e.preventDefault();
      var $this = $(this).parent();
      $this.find('button.submit-btn').prop('disabled', true);
      $this.find('.alert-info').show();
      $this.find('.alert-info p').html($('.authdata').val());
      $.ajax({
        method: "POST",
        url: $(this).prop('action'),
        data: new FormData(this),
        dataType: 'JSON',
        contentType: false,
        cache: false,
        processData: false,
        success: function (data) {
          if ((data.errors)) {
            $this.find('.alert-success').hide();
            $this.find('.alert-info').hide();
            $this.find('.alert-danger').show();
            $this.find('.alert-danger ul').html('');
            for (var error in data.errors) {
              $this.find('.alert-danger p').html(data.errors[error]);
            }
          } else {
            $this.find('.alert-info').hide();
            $this.find('.alert-danger').hide();
            $this.find('.alert-success').show();
            $this.find('.alert-success p').html(data);
            $this.find('input[type=email]').val('');
          }
            $this.find('button.submit-btn').prop('disabled', false);
        }

      });

    });




    $("#mforgotform").on('submit', function (e) {
      e.preventDefault();
      var $this = $(this).parent();
      $this.find('button.submit-btn').prop('disabled', true);
      $this.find('.alert-info').show();
      $this.find('.alert-info p').html($('.fauthdata').val());
      $.ajax({
        method: "POST",
        url: $(this).prop('action'),
        data: new FormData(this),
        dataType: 'JSON',
        contentType: false,
        cache: false,
        processData: false,
        success: function (data) {
          if ((data.errors)) {
            $this.find('.alert-success').hide();
            $this.find('.alert-info').hide();
            $this.find('.alert-danger').show();
            $this.find('.alert-danger ul').html('');
            for (var error in data.errors) {
              $this.find('.alert-danger p').html(data.errors[error]);
            }
          } else {
            $this.find('.alert-info').hide();
            $this.find('.alert-danger').hide();
            $this.find('.alert-success').show();
            $this.find('.alert-success p').html(data);
            $this.find('input[type=email]').val('');
          }
          $this.find('button.submit-btn').prop('disabled', false);
        }

      });

    });

    // FORGOT FORM ENDS

// REPORT FORM


$("#reportform").on('submit',function(e){
  e.preventDefault();
  $('.gocover').show();
  var $reportform = $(this);
  $reportform.find('button.submit-btn').prop('disabled',true);
      $.ajax({
       method:"POST",
       url:$(this).prop('action'),
       data:new FormData(this),
       dataType:'JSON',
       contentType: false,
       cache: false,
       processData: false,
       success:function(data)
       {
          if ((data.errors)) {

            for(var error in data.errors)
            {
              $reportform.find('.alert-danger').show();
              $reportform.find('.alert-danger p').html(data.errors[error]);
            }
          }
          else
          {

          $reportform.find('input[type=text],textarea').val('');

          $('#report-modal').modal('hide');
          toastr.success('Report Submitted Successfully.');

          }

                  $('.gocover').hide();
                  $reportform.find('button.submit-btn').prop('disabled',false);

       }

      });

});


// REPORT FORM ENDS



    //  USER FORM SUBMIT SECTION

    $(document).on('submit','#userform',function(e){
      e.preventDefault();
      $('.gocover').show();
      $('button.submit-btn').prop('disabled',true);
          $.ajax({
           method:"POST",
           url:$(this).prop('action'),
           data:new FormData(this),
           contentType: false,
           cache: false,
           processData: false,
           success:function(data)
           {
              if ((data.errors)) {
              $('.alert-success').hide();
              $('.alert-danger').show();
              $('.alert-danger ul').html('');
                for(var error in data.errors)
                {
                  $('.alert-danger ul').append('<li>'+ data.errors[error] +'</li>')
                }
                $('#userform input[type=text], #userform input[type=email], #userform textarea').eq(0).focus();
              }
              else
              {
                $('.alert-danger').hide();
                $('.alert-success').show();
                $('.alert-success p').html(data);
                $('#userform input[type=text], #userform input[type=email], #userform textarea').eq(0).focus();
              }
              $('.gocover').hide();
              $('button.submit-btn').prop('disabled',false);
           }

          });

    });

    // USER FORM SUBMIT SECTION ENDS

    // Pagination Starts

    // $(document).on('click', '.pagination li', function (event) {
    //   event.preventDefault();
    //   if ($(this).find('a').attr('href') != '#') {
    //     $('#preloader').show();
    //     $('#ajaxContent').load($(this).find('a').attr('href'), function (response, status, xhr) {
    //       if (status == "success") {
    //         $('#preloader').hide();
    //         $("html,body").animate({
    //           scrollTop: 0
    //         }, 1);
    //       }
    //     });
    //   }
    // });

    // Pagination Ends

        // IMAGE UPLOADING :)

        $(".upload").on( "change", function() {
          var imgpath = $(this).parent().parent().prev().find('img');
          var file = $(this);
          readURL(this,imgpath);
        });

        function readURL(input,imgpath) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                  imgpath.attr('src',e.target.result);
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        // IMAGE UPLOADING ENDS :)

// MODAL SHOW

$("#show-forgot").on('click',function(){
  $("#comment-log-reg").modal("hide");
  $("#forgot-modal").modal("show");
});

$("#show-forgot1").on('click',function(){
  $("#vendor-login").modal("hide");
  $("#forgot-modal").modal("show");
});

$("#show-login").on('click',function(){
    $("#forgot-modal").modal("hide");
    $("#comment-log-reg").modal("show");
});

// MODAL SHOW ENDS

// Catalog Search Options

// $('.check-cat').on('change',function(){
//   var len = $('input.check-cat').filter(':checked').length;
//   if(len == 0){
//     $("#catalogform").attr('action','');
//     $('.check-cat').removeAttr("name");
//   }
//   else{
//     var search = $("#searchform").val();
//     $("#catalogform").attr('action',search);
//     $('.check-cat').attr('name','cat_id[]');
//   }
//
// });

$('#category_select').on('change',function(){
  var val = $(this).val();
  $('#category_id').val(val);
  if ( $('#vendor_name').val() != null || typeof $('#vendor_name').val() !== "undefined" ) {
    $('#searchForm').attr('action', mainurl+'/shop/'+ $('#vendor_name').val()+'/'+$(this).val());
  } else {
     $('#searchForm').attr('action', mainurl+'/category/'+$(this).val());
  }
});

// Catalog Search Options Ends


// Auto Complete Section
/*
  $('#prod_name').on('keyup',function(){
     var search = encodeURIComponent($(this).val());
      if(search == ""){
        $(".autocomplete").hide();
      }
      else{ 
        $(".autocomplete").show();
        $("#myInputautocomplete-list").load(mainurl+'/autosearch/product/'+search);

      }
    });
*/
// Auto Complete Section Ends

// Auto Complete 

	$('#btnLocation').on('click',function(){
		var val = $('#search-location').val();
		if (!$(".autocomplete").is(':visible')){
			$(".autocomplete").show();
			$(".autocomplete-items .select-location").each(function( index ) {
				if(val == $( this ).data('val')){
					$( this ).css({'color' : 'red'});
					//console.log( index + ": " + $( this ).data('val') );
				}
				else{
					$( this ).css({'color' : 'black'});
				}
			});
		}
		else{
			$(".autocomplete").hide();
		}
		
		var count = $(".autocomplete-items").children().length;
		if(!(count > 0)){
			$(".autocomplete").show();
			var url = mainurl+'/autoprovince/province/' + val;
			//console.log(url);
			$("#myInputautocomplete-list").load(url);
		}
	});
	
	$(document).on('click', '.select-location', function(e){
		var val = $(this).data('val');
		$("#search-location").val(val);
		//console.log($("#btnLocation").val());
		var x = e.pageX;
		var y = e.pageY - 100;
		//console.log('x ' + x + ' - y ' + y);
		$('#provinceText1' + val).animate({
			'margin-left': '400px',
			'margin-top': -y + 'px',
			opacity: '0.3',
		}, 300, function(){
			$(".autocomplete").hide();
			$('#provinceText1' + val).css({
				'margin-left': '0px',
				'margin-top': '0px',
				opacity: '1',
			});
		});
	});
	
// Auto Complete Section Ends

// Quick View Section

    $(document).on('click', '.quick-view', function(){
      var $this = $("#quickview");
      $this.find('.modal-header').hide();
      $this.find('.modal-body').hide();
      $this.find('.modal-content').css('border','none');
        $('.submit-loader').show();
        $(".quick-view-modal").load($(this).data('href'),function(response, status, xhr){
          if(status == "success")
          $('.quick-zoom').on('load', function(){
          $('.submit-loader').hide();
              $this.find('.modal-header').show();
              $this.find('.modal-body').show();
              $this.find('.modal-content').css('border','1px solid #00000033');
    $('.quick-all-slider').owlCarousel({
        loop: true,
        dots: false,
        nav: true,
        navText: ["<i class='fa fa-angle-left'></i>", "<i class='fa fa-angle-right'></i>"],
        margin: 0,
        autoplay: false,
        items: 4,
        autoplayTimeout: 6000,
        smartSpeed: 1000,
        responsive: {
            0: {
                items: 4
            },
            768: {
                items: 4
            }
        }
    });
  });
        });

              return false;

    });
// Quick View Section Ends

// Currency and Language Section

        $(".selectors").on('change',function () {
          var url = $(this).val();
          window.location = url;
        });

// Currency and Language Section Ends


// Wishlist Section

    $(document).on('click', '.add-to-wish', function(){
        $.get( $(this).data('href') , function( data ) {

            if(data[0] == 1) {
              toastr.success(langg.add_wish);
              $('#wishlist-count').html(data[1]);

              }
            else {

              toastr.error(langg.already_wish);
              }

        });

              return false;
    });

    $(document).on('click', '#wish-btn', function(){

              return false;

    });


    $(document).on('click', '.wishlist-remove', function(){
      $(this).parent().parent().remove();
        $.get( $(this).data('href') , function( data ) {
          $('#wishlist-count').html(data[1]);
          toastr.success(langg.wish_remove);
        });
    });

// Wishlist Section Ends




// Compare Section

    $(document).on('click', '.add-to-compare', function(){
        $.get( $(this).data('href') , function( data ) {
            $("#compare-count").html(data[1]);
            if(data[0] == 0) {
                                                              toastr.success(langg.add_compare);
              }
            else {
                                                              toastr.error(langg.already_compare);
              }

        });
              return false;
    });


    $(document).on('click', '.compare-remove', function(){
      var class_name = $(this).attr('data-class');
        $.get( $(this).data('href') , function( data ) {
            $("#compare-count").html(data[1]);
            if(data[0] == 0) {
          $('.'+class_name).remove();
                                                              toastr.success(langg.compare_remove);
              }
            else {
          $('h2.title').html(langg.lang60);
          $('.compare-page-content-wrap').remove();
          $('.'+class_name).remove();
                                                             toastr.success(langg.compare_remove);
              }


        });
    });

// Compare Section Ends



// Cart Section

    $(document).on('click', '.add-to-cart', function(){



        $.get( $(this).data('href') , function( data ) {

            if(data == 'digital') {
              toastr.error(langg.already_cart);
             }
            else if(data == 0) {
              toastr.error(langg.out_stock);
              }
            else {
              $("#cart-count").html(data[0]);
              $("#cart-items").load(mainurl+'/carts/view');
              toastr.success(langg.add_cart);
              }
        });
                    return false;
    });


    $(document).on('click', '.cart-remove', function(){
      var $selector = $(this).data('class');
      $('.'+$selector).hide();
        $.get( $(this).data('href') , function( data ) {
            if(data == 0) {
                $("#cart-count").html(data);
               $('.cart-table').html('<h3 class="mt-1 pl-3 text-left">Cart is empty.</h3>');
                $('#cart-items').html('<p class="mt-1 pl-3 text-left">Cart is empty.</p>');
                $('.cartpage .col-lg-4').html('');
              }
            else {
               $('.cart-quantity').html(data[1]);
               $('.cart-total').html(data[0]);
               $('.coupon-total').val(data[0]);
               $('.main-total').html(data[3]);
              }

        });
    });

// Adding Muliple Quantity Starts

    var sizes = "";
    var size_qty = "";
    var size_price = "";
    var size_key = "";
    var colors = "";
    var total = "";
    var stock = $("#stock").val();
    var keys = "";
    var values = "";
    var prices = "";

    // Product Details Product Size Active Js Code
    $(document).on('click', '.product-size .siz-list .box', function () {
        $('.qttotal').html('1');
        var parent = $(this).parent();
        size_qty = $(this).find('.size_qty').val();
        size_price = $(this).find('.size_price').val();
        size_key = $(this).find('.size_key').val();
        sizes = $(this).find('.size').val();
        $('.product-size .siz-list li').removeClass('active');
        parent.addClass('active');
        total = getAmount()+parseFloat(size_price);
        // total =  total.toFixed(2);

        total =  thousands_separators(total);
        
        stock = size_qty;

        var pos = $('#curr_pos').val();
        var sign = $('#curr_sign').val();
        if(pos == '0')
        {
          $('#sizeprice').html(sign+total);
        }
        else {
          $('#sizeprice').html(total+sign);
        }

    });

    // Product Details Attribute Code

$(document).on('change','.product-attr',function(){

         var total = 0;
         total = getAmount()+getSizePrice();
         total = total.toFixed(2);
         var pos = $('#curr_pos').val();
         var sign = $('#curr_sign').val();
         if(pos == '0')
         {
         $('#sizeprice').html(sign+total);
         }
         else {
         $('#sizeprice').html(total+sign);
         }
});


function getSizePrice()
{

  var total = 0;
  if($('.product-size .siz-list li').length > 0)
  {
    total = parseFloat($('.product-size .siz-list li.active').find('.size_price').val());
  }

  return total;
}


function getAmount()
{
  var total = 0;
  var value = parseFloat($('#product_price').val());
  var datas = $(".product-attr:checked").map(function() {
     return $(this).data('price');
  }).get();

  var data;
  for (data in datas) {
    total += parseFloat(datas[data]);
  }
  total += value;
  return total;
}



    // Product Details Product Color Active Js Code
    $(document).on('click', '.product-color .color-list .box', function () {
        colors = $(this).data('color');
        var parent = $(this).parent();
            $('.product-color .color-list li').removeClass('active');
            parent.addClass('active');
    });

// COMMENT FORM

$(document).on('submit','#comment-form',function(e){
  e.preventDefault();
  $('#comment-form button.submit-btn').prop('disabled',true);
      $.ajax({
       method:"POST",
       url:$(this).prop('action'),
       data:new FormData(this),
       contentType: false,
       cache: false,
       processData: false,
       success:function(data)
       {
          $("#comment_count").html(data[4]);
          $('#comment-form textarea').val('');
          $('.all-comment').prepend('<li>'+
                          '<div class="single-comment comment-section">'+
                          '<div class="left-area">'+
                          '<img src="'+ data[0] +'" alt="">'+
                          '<h5 class="name">'+ data[1] +'</h5>'+
                          '<p class="date">'+data[2]+'</p>'+
                          '</div>'+
                          '<div class="right-area">'+
                          '<div class="comment-body">'+
                          '<p>'+data[3]+'</p>'+
                          '</div>'+
                          '<div class="comment-footer">'+
                          '<div class="links">'+
                        '<a href="javascript:;" class="comment-link reply mr-2"><i class="fas fa-reply "></i>'+langg.lang107+'</a>'+
                        '<a href="javascript:;" class="comment-link edit mr-2"><i class="fas fa-edit "></i>'+langg.lang111+'</a>'+
                        '<a href="javascript:;" data-href="'+data[5]+'" class="comment-link comment-delete mr-2">'+
                          '<i class="fas fa-trash"></i>'+langg.lang112+'</a>'+
                          '</div>'+
                          '</div>'+
                          '</div>'+
                          '</div>'+
                      '<div class="replay-area edit-area">'+
                        '<form class="update" action="'+data[6]+'" method="POST">'+
                          '<input type="hidden" name="_token" value="'+$('input[name=_token]').val()+'">'+
                          '<textarea placeholder="'+langg.lang113+'" name="text" required=""></textarea>'+
                          '<button type="submit">'+langg.lang114+'</button>'+
                          '<a href="javascript:;" class="remove">'+langg.lang115+'</a>'+
                        '</form>'+
                      '</div>'+
                      '<div class="replay-area reply-reply-area">'+
                        '<form class="reply-form" action="'+data[7]+'" method="POST">'+
                        '<input type="hidden" name="user_id" value="'+data[8]+'">'+
                          '<input type="hidden" name="_token" value="'+$('input[name=_token]').val()+'">'+
                          '<textarea placeholder="'+langg.lang117+'" name="text" required=""></textarea>'+
                          '<button type="submit">'+langg.lang114+'</button>'+
                          '<a href="javascript:;" class="remove">'+langg.lang115+'</a>'+
                        '</form>'+
                      '</div>'+
                          '</li>');

          $('#comment-form button.submit-btn').prop('disabled',false);
       }

      });
});

// COMMENT FORM ENDS

// REPLY FORM

$(document).on('submit','.reply-form',function(e){
  e.preventDefault();
    var btn = $(this).find('button[type=submit]');
    btn.prop('disabled',true);
    var $this = $(this).parent();
    var text = $(this).find('textarea');
      $.ajax({
       method:"POST",
       url:$(this).prop('action'),
       data:new FormData(this),
       contentType: false,
       cache: false,
       processData: false,
       success:function(data)
       {
          $('#comment-form textarea').val('');
          $('button.submit-btn').prop('disabled',false);
                      $this .before('<div class="single-comment replay-review">'+
                          '<div class="left-area">'+
                          '<img src="'+ data[0] +'" alt="">'+
                          '<h5 class="name">'+ data[1] +'</h5>'+
                          '<p class="date">'+data[2]+'</p>'+
                          '</div>'+
                          '<div class="right-area">'+
                          '<div class="comment-body">'+
                          '<p>'+data[3]+'</p>'+
                          '</div>'+
                          '<div class="comment-footer">'+
                          '<div class="links">'+
                        '<a href="javascript:;" class="comment-link reply mr-2"><i class="fas fa-reply "></i>'+langg.lang107+'</a>'+
                        '<a href="javascript:;" class="comment-link edit mr-2"><i class="fas fa-edit "></i>'+langg.lang111+'</a>'+
                        '<a href="javascript:;" data-href="'+data[4]+'" class="comment-link reply-delete mr-2">'+
                          '<i class="fas fa-trash"></i>'+langg.lang112+'</a>'+
                          '</div>'+
                          '</div>'+
                          '</div>'+
                          '</div>'+
                      '<div class="replay-area edit-area">'+
                        '<form class="update" action="'+data[5]+'" method="POST">'+
                          '<input type="hidden" name="_token" value="'+$('input[name=_token]').val()+'">'+
                          '<textarea placeholder="'+langg.lang116+'" name="text" required=""></textarea>'+
                          '<button type="submit">'+langg.lang114+'</button>'+
                          '<a href="javascript:;" class="remove">'+langg.lang115+'</a>'+
                        '</form>'+
                      '</div>');
          $this.toggle();
          text.val('');
          btn.prop('disabled',false);
       }

      });
});

// REPLY FORM ENDS

// EDIT
$(document).on('click','.edit',function(){
  var text = $(this).parent().parent().prev().find('p').html();
  text = $.trim(text);
  $(this).parent().parent().parent().parent().next('.edit-area').find('textarea').val(text);
  $(this).parent().parent().parent().parent().next('.edit-area').toggle();
});
// EDIT ENDS

// UPDATE
$(document).on('submit','.update',function(e){
  e.preventDefault();
  var btn = $(this).find('button[type=submit]');
  var text = $(this).parent().prev().find('.right-area .comment-body p');
  var $this = $(this).parent();
  btn.prop('disabled',true);
      $.ajax({
       method:"POST",
       url:$(this).prop('action'),
       data:new FormData(this),
       contentType: false,
       cache: false,
       processData: false,
       success:function(data)
       {
        text.html(data);
        $this.toggle();
        btn.prop('disabled',false);
       }
      });
});
// UPDATE ENDS

// COMMENT DELETE
$(document).on('click','.comment-delete',function(){
  var count = parseInt($("#comment_count").html());
  count--;
  $("#comment_count").html(count);
  $(this).parent().parent().parent().parent().parent().remove();
  $.get($(this).data('href'));
});
// COMMENT DELETE ENDS


// COMMENT REPLY
$(document).on('click','.reply',function(){
  $(this).parent().parent().parent().parent().parent().show().find('.reply-reply-area').show();
  $(this).parent().parent().parent().parent().parent().show().find('.reply-reply-area .reply-form textarea').focus();

});
// COMMENT REPLY ENDS

// REPLY DELETE
$(document).on('click','.reply-delete',function(){
  $(this).parent().parent().parent().parent().remove();
  $.get($(this).data('href'));
});
// REPLY DELETE ENDS

// View Replies
$(document).on('click','.view-reply',function(){
  $(this).parent().parent().parent().parent().siblings('.replay-review').removeClass('hidden');

});
// View Replies ENDS

// CANCEL CLICK

$(document).on('click','#comment-area .remove',function(){
  $(this).parent().parent().hide();
});

// CANCEL CLICK ENDS



    /*-----------------------------
        Cart Page Quantity
    -----------------------------*/
    $(document).on('click', '.qtminus', function () {
        var el = $(this);
        var $tselector = el.parent().parent().find('.qttotal');
        total = $($tselector).text();
        if (total > 1) {
            total--;
        }
        $($tselector).text(total);
    });

    $(document).on('click', '.qtplus', function () {
        var el = $(this);
        var $tselector = el.parent().parent().find('.qttotal');
        total = $($tselector).text();
        if(stock != "")
        {
            var stk = parseInt(stock);
              if(total < stk)
              {
                 total++;
                 $($tselector).text(total);
              }
        }
        else {
        total++;
        }

        $($tselector).text(total);
    });




    $(document).on("click", "#addcrt" , function(){
     var qty = $('.qttotal').html();
     var pid = $(this).parent().parent().parent().parent().find("#product_id").val();

      if($('.product-attr').length > 0)
      {
        values = $(".product-attr:checked").map(function() {
        return $(this).val();
      }).get();

      keys = $(".product-attr:checked").map(function() {
        return $(this).data('key');
      }).get();

      prices = $(".product-attr:checked").map(function() {
        return $(this).data('price');
      }).get();



      }

      $.ajax({
        type: "GET",
        url:mainurl+"/addnumcart",
        data:{id:pid,qty:qty,size:sizes,color:colors,size_qty:size_qty,size_price:size_price,size_key:size_key,keys:keys,values:values,prices:prices},
        success:function(data){

          if(data == 'digital') {
              toastr.error(langg.already_cart);
            }
          else if(data == 0) {
              toastr.error(langg.out_stock);
            }
          else {
            $("#cart-count").html(data[0]);
            $("#cart-items").load(mainurl+'/carts/view');
              toastr.success(langg.add_cart);
            }
            }
        });

    });




    $(document).on("click", "#qaddcrt" , function(){
      var qty = $('.qttotal').html();
      var pid = $(this).parent().parent().parent().parent().find("#product_id").val();

      if($('.product-attr').length > 0)
      {
        values = $(".product-attr:checked").map(function() {
          return $(this).val();
      }).get();

      keys = $(".product-attr:checked").map(function() {
          return $(this).data('key');
      }).get();

      prices = $(".product-attr:checked").map(function() {
          return $(this).data('price');
      }).get();

      }

      window.location = mainurl+"/addtonumcart?id="+pid+"&qty="+qty+"&size="+sizes+"&color="+colors.substring(1, colors.length)+"&size_qty="+size_qty+"&size_price="+size_price+"&size_key="+size_key+"&keys="+keys+"&values="+values+"&prices="+prices;

     });

// Adding Muliple Quantity Ends

// Add By ONE

      $(document).on("click", ".adding" , function(){
        var pid =  $(this).parent().parent().find('.prodid').val();
        var itemid =  $(this).parent().parent().find('.itemid').val();
        var size_qty = $(this).parent().parent().find('.size_qty').val();
        var size_price = $(this).parent().parent().find('.size_price').val();
        var stck = $("#stock"+itemid).val();
        var qty = $("#qty"+itemid).html();
        if(stck != "")
        {
        var stk = parseInt(stck);
          if(qty < stk)
          {
             qty++;
         $("#qty"+itemid).html(qty);
          }
        }
        else{
         qty++;
         $("#qty"+itemid).html(qty);
        }
            $.ajax({
                    type: "GET",
                    url:mainurl+"/addbyone",
                    data:{id:pid,itemid:itemid,size_qty:size_qty,size_price:size_price},
                    success:function(data){
                        if(data == 0)
                        {
                        }
                        else
                        {
                        $(".discount").html($("#d-val").val());
                        $(".cart-total").html(data[0]);
                        $(".main-total").html(data[3]);
                        $(".coupon-total").val(data[3]);
                        $("#prc"+itemid).html(data[2]);
                        $("#prct"+itemid).html(data[4]);
                        $("#cqt"+itemid).html(data[1]);
                        $("#qty"+itemid).html(data[1]);
                        }
                      }
              });
       });

// Reduce By ONE

      $(document).on("click", ".reducing" , function(){

        $('.xloader').removeClass('d-none');


        var pid =  $(this).parent().parent().find('.prodid').val();
        var itemid =  $(this).parent().parent().find('.itemid').val();
        var size_qty = $(this).parent().parent().find('.size_qty').val();
        var size_price = $(this).parent().parent().find('.size_price').val();
        var stck = $("#stock"+itemid).val();
        var qty = $("#qty"+itemid).html();
        qty--;


        if(qty < 1)
         {
         $("#qty"+itemid).html("1");
         }
         else{
         $("#qty"+itemid).html(qty);
            $.ajax({
                    type: "GET",
                    url:mainurl+"/reducebyone",
                    data:{id:pid,itemid:itemid,size_qty:size_qty,size_price:size_price},
                    success:function(data){
                        $(".discount").html($("#d-val").val());
                        $(".cart-total").html(data[0]);
                        $(".main-total").html(data[3]);
                        $(".coupon-total").val(data[3]);
                        $("#prc"+itemid).html(data[2]);
                        $("#prct"+itemid).html(data[4]);
                        $("#cqt"+itemid).html(data[1]);
                        $("#qty"+itemid).html(data[1]);
                      }
              });
         }
        //  $('.xloader').addClass('d-none');
       });

// Coupon Form

    $("#coupon-form").on('submit', function () {
        var val = $("#code").val();
        var total = $("#grandtotal").val();
            $.ajax({
                    type: "GET",
                    url:mainurl+"/carts/coupon",
                    data:{code:val, total:total},
                    success:function(data){
                        if(data == 0)
                        {
                            toastr.error(langg.no_coupon);
                            $("#code").val("");
                        }
                        else if(data == 2)
                        {
                            toastr.error(langg.already_coupon);
                            $("#code").val("");
                        }
                        else
                        {
                            $("#coupon_form").toggle();
                            $(".main-total").html(data[0]);
                            $(".discount").html(data[4]);
                                        toastr.success(langg.coupon_found);
                            $("#code").val("");
                        }
                      }
              });
              return false;
    });



// Cart Section Ends

// Cart Page Section

       $(document).on("change", ".color" , function(){
        var id =  $(this).parent().find('input[type=hidden]').val();
        var colors = $(this).val();
        $(this).css('background',colors);
            $.ajax({
                    type: "GET",
                    url:mainurl+"/upcolor",
                    data:{id:id,color:colors},
                    success:function(data){
                                                              toastr.success(langg.color_change);
                      }
              });
       });


// Cart Page Section Ends

// Review Section

    $(document).on('click','.stars', function(){
      $('.stars').removeClass('active');
      $(this).addClass('active');
      $('#rating').val($(this).data('val'));

    });

    $(document).on('submit','#reviewform',function(e){
      var $this = $(this);
      e.preventDefault();
      $('.gocover').show();
      $('button.submit-btn').prop('disabled',true);
          $.ajax({
           method:"POST",
           url:$(this).prop('action'),
           data:new FormData(this),
           contentType: false,
           cache: false,
           processData: false,
           success:function(data)
           {
              if ((data.errors)) {
              $('.alert-success').hide();
              $('.alert-danger').show();
              $('.alert-danger ul').html('');
                for(var error in data.errors)
                {
                  $('.alert-danger ul').append('<li>'+ data.errors[error] +'</li>')
                }
                $('#reviewform textarea').eq(0).focus();

              }
              else
              {
                $('.alert-danger').hide();
                $('.alert-success').show();
                $('.alert-success p').html(data[0]);
                $('#star-rating').html(data[1]);
                $('#reviewform textarea').eq(0).focus();
                $('#reviewform textarea').val('');
                $('#reviews-section').load($this.data('href'));
              }
              $('.gocover').hide();
              $('button.submit-btn').prop('disabled',false);
           }

          });
    });

// Review Section Ends


// MESSAGE FORM

$(document).on('submit','#messageform',function(e){
  e.preventDefault();
  var href = $(this).data('href');
  $('.gocover').show();
  $('button.mybtn1').prop('disabled',true);
      $.ajax({
       method:"POST",
       url:$(this).prop('action'),
       data:new FormData(this),
       contentType: false,
       cache: false,
       processData: false,
       success:function(data)
       {
          if ((data.errors)) {
          $('.alert-success').hide();
          $('.alert-danger').show();
          $('.alert-danger ul').html('');
            for(var error in data.errors)
            {
              $('.alert-danger ul').append('<li>'+ data.errors[error] +'</li>')
            }
            $('#messageform textarea').val('');
          }
          else
          {
            $('.alert-danger').hide();
            $('.alert-success').show();
            $('.alert-success p').html(data);
            $('#messageform textarea').val('');
            $('#messages').load(href);
          }
          $('.gocover').hide();
          $('button.mybtn1').prop('disabled',false);
       }
      });
});

// MESSAGE FORM ENDS

//**************************** CUSTOM JS SECTION ENDS****************************************

        $(document).on("click", ".favorite-prod" , function(){
          var $this = $(this);
            $.get( $(this).data('href'));
              $this.html('<i class="icofont-check"></i> Favorite');
              $this.prop('class','');

            });


//**************************** GLOBAL CAPCHA****************************************

        $('.refresh_code').on( "click", function() {
            $.get(mainurl+'/contact/refresh_code', function(data, status){
                $('.codeimg1').attr("src",mainurl+"/assets/images/capcha_code.png?time="+ Math.random());
            });
        })

//**************************** GLOBAL CAPCHA ENDS****************************************

//**************************** VENDOR MODAL****************************************

        $('#nav-log-tab11').on( "click", function() {
          $('#vendor-login .modal-dialog').removeClass('modal-lg');
        });

        $('#nav-reg-tab11').on( "click", function() {
          $('#vendor-login .modal-dialog').addClass('modal-lg');
        });

//**************************** VENDOR MODAL ENDS****************************************

$(document).on('click','.affilate-btn',function(e){
  e.preventDefault();
  window.open($(this).data('href'), '_blank');

});

$(document).on('click','.add-to-cart-quick',function(e){
  e.preventDefault();
  window.location = $(this).data('href');

});


// TRACK ORDER

$('#track-form').on('submit',function(e){
  e.preventDefault();
  var code = $('#track-code').val();
  $('.submit-loader').removeClass('d-none');
  $('#track-order').load(mainurl+'/order/track/'+code,function(response, status, xhr){
  if(status == "success")
  {
        $('.submit-loader').addClass('d-none');
  }
});
});

$(document).on('click','.select-bank',function(){
  $('.submit-loader').show();
    $('#modal1').find('.modal-title').html('THANH TOÁN');
    $('#modal1 .modal-content .modal-body').html('').load($(this).attr('data-href'),function(response, status, xhr){
        if(status == "success")
        {
          $('.submit-loader').hide();
        }
  
      });
  });

// TRACK ORDER ENDS

//PLACE
$('.province').on('change',function(e){
  $(".district").empty();
  $(".ward").empty();
  var sldistrict = $(".district")[0];
  var option = document.createElement("option");
  option.text = "Chọn Quận/Huyện";
  option.value = "";
  sldistrict.add(option);
  var slward = $(".ward")[0];
  var option_ward = document.createElement("option");
  option_ward.text = "Chọn Phường/Xã";
  option_ward.value = "";
  slward.add(option_ward);
  var url = mainurl+'/districts/'+($(this).val());
  $.ajax({
    type:"GET",
        url:url,
        data:{},
        success:function(data)
        {
          if ((data.errors)) {
            console.log(data.errors);
          }
          else
          {
            data.sort(function(a, b) {
                var x = a.name.toLowerCase(), y = b.name.toLowerCase();
                return x < y ? -1 : x > y ? 1 : 0;
            });
            $.each(data, function( i, val ) {
                var opt = document.createElement("option");
                opt.text = val.name;
                opt.value = val.id;
                sldistrict.add(opt);
            });
          }
        }
    });
});

$('.province_sub').on('change',function(e){
  $(".district_sub").empty();
  $(".ward_sub").empty();
  var sldistrict_sub = $(".district_sub")[0];
  var option = document.createElement("option");
  option.text = "Chọn Quận/Huyện";
  option.value = "";
  sldistrict_sub.add(option);
  var slward_sub = $(".ward_sub")[0];
  var option_ward = document.createElement("option");
  option_ward.text = "Chọn Phường/Xã";
  option_ward.value = "";
  slward_sub.add(option_ward);
  var url = mainurl+'/districts/'+($(this).val());
  $.ajax({
    type:"GET",
        url:url,
        data:{},
        success:function(data)
        {
          if ((data.errors)) {
            console.log(data.errors);
          }
          else
          {
            data.sort(function(a, b) {
                var x = a.name.toLowerCase(), y = b.name.toLowerCase();
                return x < y ? -1 : x > y ? 1 : 0;
            });
            $.each(data, function( i, val ) {
                var opt = document.createElement("option");
                opt.text = val.name;
                opt.value = val.id;
                sldistrict_sub.add(opt);
            });
          }
        }
    });
});

//select district
$('.district').on('change',function(e){
  $(".ward").empty();
  var slward = $(".ward")[0];
  var option_ward = document.createElement("option");
  option_ward.text = "Chọn Phường/Xã";
  option_ward.value = "";
  slward.add(option_ward);
	var district_id = $(this).val();
  var url1 = mainurl+'/wards/'+district_id;
  $.ajax({
    type:"GET",
    url:url1,
    data:{},
    success:function(data)
    {
      if ((data.errors)) {
        console.log(data.errors);
      }
      else
      {
        data.sort(function(a, b) {
            var x = a.name.toLowerCase(), y = b.name.toLowerCase();
            return x < y ? -1 : x > y ? 1 : 0;
        });
        $.each(data, function( i, val ) {
            var opt = document.createElement("option");
            opt.text = val.name;
            opt.value = val.id;
            slward.add(opt);
        });
      }
    },
    error: function(XMLHttpRequest, textStatus, errorThrown) {
        console.log(XMLHttpRequest);
        console.log(textStatus);
        console.log(errorThrown);
    }
  });
});

$('.district_sub').on('change',function(e){
  $(".ward_sub").empty();
  var slward = $(".ward_sub")[0];
  var option_ward = document.createElement("option");
  option_ward.text = "Chọn Phường/Xã";
  option_ward.value = "";
  slward.add(option_ward);
	var district_id = $(this).val();
  var url1 = mainurl+'/wards/'+district_id;
  $.ajax({
    type:"GET",
    url:url1,
    data:{},
    success:function(data)
    {
      if ((data.errors)) {
        console.log(data.errors);
      }
      else
      {
        data.sort(function(a, b) {
            var x = a.name.toLowerCase(), y = b.name.toLowerCase();
            return x < y ? -1 : x > y ? 1 : 0;
        });
        $.each(data, function( i, val ) {
            var opt = document.createElement("option");
            opt.text = val.name;
            opt.value = val.id;
            slward.add(opt);
        });
      }
    },
    error: function(XMLHttpRequest, textStatus, errorThrown) {
        console.log(XMLHttpRequest);
        console.log(textStatus);
        console.log(errorThrown);
    }
  });
});
//select district end
//PLACE END

  //init_search_data();

  var currentFocus;
  var timer_delay = simpleDelayTimer();
  $(document).on('input','#prod_name',function(e){
    if (document.getElementById('prod_name').value.trim() == "")
      return;
    timer_delay.reset();
    timer_delay.start(this);
  });

  $(document).on('keydown','#prod_name',function(e){
    var x = document.getElementById(this.id + "autocomplete-search-list");
        if (x) x = x.getElementsByTagName("div");
        if (e.keyCode == 40) {
            /*If the arrow DOWN key is pressed,
            increase the currentFocus variable:*/
            currentFocus++;
            /*and and make the current item more visible:*/
            addActive(x);
        } else if (e.keyCode == 38) { //up
            /*If the arrow UP key is pressed,
            decrease the currentFocus variable:*/
            currentFocus--;
            /*and and make the current item more visible:*/
            addActive(x);
        } else if (e.keyCode == 13) {
        /*If the ENTER key is pressed, prevent the form from being submitted,*/
            var xxx = document.getElementsByClassName("autocomplete-search-items");
            if ($(xxx).children().length > 0) {
                e.preventDefault();
                if (currentFocus > -1) {
                    /*and simulate a click on the "active" item:*/
                    if (x) {
                        if ($(x[currentFocus]).children().first().is('a')) {
                            window.location.replace($(x[currentFocus]).children().first().attr('href'));
                        }
                        else {
                            x[currentFocus].click();
                        }
                    }
                }
            }
            
        }

  });

  function addActive(x) {
    /*a function to classify an item as "active":*/
    if (!x) return false;
    /*start by removing the "active" class on all items:*/
    removeActive(x);
    if (currentFocus >= x.length) currentFocus = 0;
    if (currentFocus < 0) currentFocus = (x.length - 1);
    /*add class "autocomplete-active":*/
    x[currentFocus].classList.add("autocomplete-search-active");
}
function removeActive(x) {
    /*a function to remove the "active" class from all autocomplete items:*/
    for (var i = 0; i < x.length; i++) {
        x[i].classList.remove("autocomplete-search-active");
    }
}

function simpleDelayTimer() {
  var delay = 1500;
  var timer = null;
  return {
    start:
    function (inp) {
      this.timer = setTimeout(function () {
          fetch_search_data_by_key(inp);
      }, delay, inp);
      return timer;
    },
    reset: function () {
      clearTimeout(this.timer);
    }
  }
}

function fetch_search_data_by_key(inp) {
  var keyword = document.getElementById('prod_name').value.trim();
  var url1 = mainurl+'/item/s/data/'+keyword;
  $.ajax({
    type:"GET",
    url:url1,
    data:{},
    success:function(data)
    {
      if ((data.errors)) {
        console.log(data.errors);
      }
      else
      {
        //sessionStorage.setItem('searching_data', JSON.stringify(data));
        //autocomplete(document.getElementById("prod_name"), data);
        var arr0 = data[0];
        var arr = data[1];
        var a, b, i, val = inp.value;
        /*close any already open lists of autocompleted values*/
        closeAllLists();
        if (!val) { return false; }
        currentFocus = -1;
        /*create a DIV element that will contain the items (values):*/
        a = document.createElement("DIV");
        a.setAttribute("id", inp.id + "autocomplete-search-list");
        a.setAttribute("class", "autocomplete-search-items");
        /*append the DIV element as a child of the autocomplete container:*/
        inp.parentNode.appendChild(a);
        var shop_count = 0;
        /*for each item in the array...*/
        for (i = 0; i < arr0.length; i++) {
            /*create a DIV element for each matching element:*/
            b = document.createElement("DIV");
            /*make the matching letters bold:*/
            var vendor_url = mainurl+'/store/' + arr0[i]["shop_name"];
            b.innerHTML = '<a href="' + vendor_url +'"><strong style="padding-left: 60px; font-style: italic;">Shop: ' + arr0[i]["name"] + "</strong></a>";
            /*insert a input field that will hold the current array item's value:*/
            b.innerHTML += "<input type='hidden' value='" + arr0[i]["name"] + "'>";
            /*execute a function when someone clicks on the item value (DIV element):*/
            b.addEventListener("click", function (e) {
                /*insert the value for the autocomplete text field:*/
                inp.value = this.getElementsByTagName("input")[0].value;
                /*close the list of autocompleted values,
                (or any other open lists of autocompleted values:*/
                closeAllLists();
                inp.focus();
            });
            a.appendChild(b);
            shop_count++;
            if (shop_count == 3) {
                break;
            }
        }
    
        // var counter = shop_count;
        // var loop = Object.keys(arr).length;
        // var limit_item = 20;
        // for (i = 0; i < loop; i++) {
        //   if(arr[i] != null){
        //     b = document.createElement("DIV");
        //     /*make the matching letters bold:*/
        //     b.innerHTML = arr[i];
        //     /*insert a input field that will hold the current array item's value:*/
        //     b.innerHTML += "<input type='hidden' value='" + arr[i] + "'>";
        //     /*execute a function when someone clicks on the item value (DIV element):*/
        //     b.addEventListener("click", function (e) {
        //         /*insert the value for the autocomplete text field:*/
        //         inp.value = this.getElementsByTagName("input")[0].value;
        //         /*close the list of autocompleted values,
        //         (or any other open lists of autocompleted values:*/
        //         closeAllLists();
        //         inp.focus();
        //     });
        //     a.appendChild(b);
        //     counter++;
        //     if (counter == limit_item) {
        //         break;
        //     }
        //   }
        //   else{
        //     limit_item+=1;
        //   }
        // }

        var counter = shop_count;
        var limit_item = 20;
        for( var key in arr ) {
          var value = arr[key];
          if (value != null) {
            b = document.createElement("DIV");
            /*make the matching letters bold:*/
            b.innerHTML = value;
            var product_url = mainurl+'/item/' + key;
            b.innerHTML = '<a href="' + product_url +'"> ' + value + "</a>";

            /*insert a input field that will hold the current array item's value:*/
            b.innerHTML += "<input type='hidden' value='" + value + "'>";
            /*execute a function when someone clicks on the item value (DIV element):*/
            b.addEventListener("click", function (e) {
                /*insert the value for the autocomplete text field:*/
                inp.value = this.getElementsByTagName("input")[0].value;
                /*close the list of autocompleted values,
                (or any other open lists of autocompleted values:*/
                closeAllLists();
                inp.focus();
            });
            a.appendChild(b);
            counter++;
            if (counter == limit_item) {
              break;
            }
          }
          else{
            limit_item+=1;
          }
        };
        
        function closeAllLists(elmnt) {
          /*close all autocomplete lists in the document,
          except the one passed as an argument:*/
          var x = document.getElementsByClassName("autocomplete-search-items");
          for (var i = 0; i < x.length; i++) {
              if (elmnt != x[i] && elmnt != inp) {
                  x[i].parentNode.removeChild(x[i]);
              }
          }
      }

        document.addEventListener("click", function (e) {
            closeAllLists(e.target);
        });
      } // end success ajax
    },
    error: function(XMLHttpRequest, textStatus, errorThrown) {
        console.log(XMLHttpRequest);
        console.log(textStatus);
        console.log(errorThrown);
    }
  });
}

$('.vnpay_bank').on('click',function(){
  $('.vnpay_bank').removeClass('active');
  var data = $(this).data('val');
  $('.BankPay').val(data);
  $(this).addClass('active');
});




});



function init_search_data() {
  if (typeof (Storage) !== 'undefined') {

      if (sessionStorage.hasOwnProperty('searching_data')) {
          var searching_data = sessionStorage.getItem('searching_data');
          autocomplete(document.getElementById("prod_name"), JSON.parse(searching_data));
      }
      else {
          fetch_search_data();
      }
  } else {
      fetch_search_data();
  }
}

function fetch_search_data() {
  var keyword = document.getElementById('prod_name').value.trim();
  var url1 = mainurl+'/item/s/data/'+keyword;
  $.ajax({
    type:"GET",
    url:url1,
    data:{},
    success:function(data)
    {
      console.log(data);
      if ((data.errors)) {
        console.log(data.errors);
      }
      else
      {
        //sessionStorage.setItem('searching_data', JSON.stringify(data));
        autocomplete(document.getElementById("prod_name"), data);

        
      }
    },
    error: function(XMLHttpRequest, textStatus, errorThrown) {
        console.log(XMLHttpRequest);
        console.log(textStatus);
        console.log(errorThrown);
    }
  });
}

function autocomplete(inp, obj) {
    /*the autocomplete function takes two arguments,
    the text field element and an array of possible autocompleted values:*/

    var arr0 = obj[0];
    var arr = obj[1];

    var currentFocus;
    /*execute a function when someone writes in the text field:*/
    inp.addEventListener("input", function (e) {
        var a, b, i, val = this.value;
        /*close any already open lists of autocompleted values*/
        closeAllLists();
        if (!val) { return false; }
        currentFocus = -1;
        /*create a DIV element that will contain the items (values):*/
        a = document.createElement("DIV");
        a.setAttribute("id", this.id + "autocomplete-search-list");
        a.setAttribute("class", "autocomplete-search-items");
        /*append the DIV element as a child of the autocomplete container:*/
        this.parentNode.appendChild(a);
        var shop_count = 0;
        /*for each item in the array...*/
        for (i = 0; i < arr0.length; i++) {
            var index_str = arr0[i]["name"].toUpperCase().indexOf(val.toUpperCase());
            if (index_str < 0) {
                index_str = arr0[i]["email"].toUpperCase().indexOf(val.toUpperCase());
            }
            /*check if the item starts with the same letters as the text field value:*/
            //if (arr[i].substr(0, val.length).toUpperCase() == val.toUpperCase()) {
            if (index_str !== -1) {
                /*create a DIV element for each matching element:*/
                b = document.createElement("DIV");
                /*make the matching letters bold:*/
                var vendor_url = mainurl+'/store/' + arr0[i]["shop_name"];
                b.innerHTML = '<a href="' + vendor_url +'"><strong style="padding-left: 60px; font-style: italic;">Shop: ' + arr0[i]["name"] + "</strong></a>";
                //b.innerHTML = "<strong>" + arr[i].substr(0, val.length) + "</strong>";
                //b.innerHTML += arr[i].substr(val.length);
                /*insert a input field that will hold the current array item's value:*/
                b.innerHTML += "<input type='hidden' value='" + arr0[i]["name"] + "'>";
                /*execute a function when someone clicks on the item value (DIV element):*/
                b.addEventListener("click", function (e) {
                    /*insert the value for the autocomplete text field:*/
                    inp.value = this.getElementsByTagName("input")[0].value;
                    /*close the list of autocompleted values,
                    (or any other open lists of autocompleted values:*/
                    closeAllLists();
                    inp.focus();
                });
                a.appendChild(b);
                shop_count++;
                if (shop_count == 3) {
                    break;
                }
            }
        }
        
        var counter = 0;
        var loop = Object.keys(arr).length;
        for (i = 0; i < loop; i++) {
            var index_str = arr[i].toUpperCase().indexOf(val.toUpperCase());
            /*check if the item starts with the same letters as the text field value:*/
            //if (arr[i].substr(0, val.length).toUpperCase() == val.toUpperCase()) {
            if (index_str !== -1) {
                /*create a DIV element for each matching element:*/
                b = document.createElement("DIV");
                /*make the matching letters bold:*/
                b.innerHTML = arr[i];
                //b.innerHTML = "<strong>" + arr[i].substr(0, val.length) + "</strong>";
                //b.innerHTML += arr[i].substr(val.length);
                /*insert a input field that will hold the current array item's value:*/
                b.innerHTML += "<input type='hidden' value='" + arr[i] + "'>";
                /*execute a function when someone clicks on the item value (DIV element):*/
                b.addEventListener("click", function (e) {
                    /*insert the value for the autocomplete text field:*/
                    inp.value = this.getElementsByTagName("input")[0].value;
                    /*close the list of autocompleted values,
                    (or any other open lists of autocompleted values:*/
                    closeAllLists();
                    inp.focus();
                });
                a.appendChild(b);
                counter++;
                if (counter == 20) {
                    break;
                }
            }
        }
    });
    /*execute a function presses a key on the keyboard:*/
    inp.addEventListener("keydown", function (e) {
        var x = document.getElementById(this.id + "autocomplete-search-list");
        if (x) x = x.getElementsByTagName("div");
        if (e.keyCode == 40) {
            /*If the arrow DOWN key is pressed,
            increase the currentFocus variable:*/
            currentFocus++;
            /*and and make the current item more visible:*/
            addActive(x);
        } else if (e.keyCode == 38) { //up
            /*If the arrow UP key is pressed,
            decrease the currentFocus variable:*/
            currentFocus--;
            /*and and make the current item more visible:*/
            addActive(x);
        } else if (e.keyCode == 13) {
        /*If the ENTER key is pressed, prevent the form from being submitted,*/
            var xxx = document.getElementsByClassName("autocomplete-search-items");
            if ($(xxx).children().length > 0) {
                e.preventDefault();
                if (currentFocus > -1) {
                    /*and simulate a click on the "active" item:*/
                    if (x) {
                        if ($(x[currentFocus]).children().first().is('a')) {
                            window.location.replace($(x[currentFocus]).children().first().attr('href'));
                        }
                        else {
                            x[currentFocus].click();
                        }
                    }
                }
            }
            
        }
    });
    function addActive(x) {
        /*a function to classify an item as "active":*/
        if (!x) return false;
        /*start by removing the "active" class on all items:*/
        removeActive(x);
        if (currentFocus >= x.length) currentFocus = 0;
        if (currentFocus < 0) currentFocus = (x.length - 1);
        /*add class "autocomplete-active":*/
        x[currentFocus].classList.add("autocomplete-search-active");
    }
    function removeActive(x) {
        /*a function to remove the "active" class from all autocomplete items:*/
        for (var i = 0; i < x.length; i++) {
            x[i].classList.remove("autocomplete-search-active");
        }
    }
    function closeAllLists(elmnt) {
        /*close all autocomplete lists in the document,
        except the one passed as an argument:*/
        var x = document.getElementsByClassName("autocomplete-search-items");
        for (var i = 0; i < x.length; i++) {
            if (elmnt != x[i] && elmnt != inp) {
                x[i].parentNode.removeChild(x[i]);
            }
        }
    }
    /*execute a function when someone clicks in the document:*/
    document.addEventListener("click", function (e) {
        closeAllLists(e.target);
    });
}

});
