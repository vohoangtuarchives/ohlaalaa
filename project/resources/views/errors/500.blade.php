@php
  $actual_path = str_replace('project','',base_path());
 if (is_dir($actual_path . '/install')) {
     //dd('install handled');
     echo '<h2>500 Internal server error!</h2>';
     //giờ tìm xem chỗ nào nó gọi file này ra
     // van de là mày ko muốn vào source install - mà muốn vào source project đúng ko?
     // open source install tao xem <coi class=""></coi>

     //ukm > nhung muon xem no check o cho nao luon de biet hoc cach no check - ok

     //tu de t run cai source index cua no m xem
     //echo '<meta http-equiv="refresh" content="0; url='.url('/install').'" />';
     //echo '<meta http-equiv="refresh" content="0; url='.url('/').'" />';
     //echo '<h1>500 Internal server error!</h1>';

 }else{
    // echo '<meta http-equiv="refresh" content="0; url='.url('/').'" />';
    echo '<h2>500 Internal server error!</h2>';
 }
@endphp

