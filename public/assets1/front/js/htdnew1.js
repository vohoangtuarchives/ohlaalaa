$(function ($) {
    "use strict";


    $(document).ready(function () {

        //load district
        $('.customer_province').on('change',function(e){
            $(".customer_district").empty();
            $(".customer_ward").empty();

            if($('.is_shipdiff').val() == 'false'){
                if(pos == 0){
                    $(".customer_shippingcost1").text('{{ $curr->sign }}'+0);
                }
                else{
                    $(".customer_shippingcost1").text(0 + '{{ $curr->sign }}');
                }

                $(".shipping_viettelpost").val(0);
                //$('.mybtn1').addClass('btn btn-secondary');
                $('.mybtn1').prop('disabled',true);
                mship = $('.shipping').val();
                shipping_change(mship);
            }


            $('.city-default').val($('.customer_province option:selected').text().trim());
            var sldistrict = $(".customer_district")[0];
            var option = document.createElement("option");
            option.text = "Select District";
            option.value = "";
            sldistrict.add(option);

            var slward = $(".customer_ward")[0];
            var option_ward = document.createElement("option");
            option_ward.text = "Select Ward";
            option_ward.value = "";
            slward.add(option_ward);
            fillDistrict(($(this).val()), sldistrict);
        });

        function fillDistrict(province_id, district_select){
            var url = mainurl+'/districts/'+province_id;
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
                            district_select.add(opt);
                        });
                      }
                   }
            });
        }

        //shipping cost calculation
        $('.customer_district').on('change',function(e){
            console.log('handled!');
            $(".customer_ward").empty();
            var slward = $(".customer_ward")[0];
            var option_ward = document.createElement("option");
            option_ward.text = "Select Ward";
            option_ward.value = "";
            slward.add(option_ward);
            var province_id = $('.customer_province').val();
            var district_id = $(this).val();
            if(district_id > 0)
                fillWard(district_id, slward);
            // if($('.is_shipdiff').val() == 'false'){
            //     fillShippingCost(province_id, district_id);
            // }
            
        });

        function fillWard(district_id, ward_select){
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
                            opt.selected = $("#auth-ward-id").val() == val.id ? 'selected' : '';
                            ward_select.add(opt);
                        });
                      }
                   },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        console.log(XMLHttpRequest);
                        console.log(textStatus);
                        console.log(errorThrown);
                    }
            });
        }


    });
});