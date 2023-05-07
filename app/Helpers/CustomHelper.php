<?php
if (!function_exists('hello')) {
    function hello($name)
    {
        return 'Hello ' . $name . '!';
    }
}

if (!function_exists('admin_log')) {
    function admin_log($name, $content = '')
    {
        $admin = \Illuminate\Support\Facades\Auth::user();

        if(isset($admin->name)){
            \App\Models\AdminTransaction::create([
                'admin_id' => $admin->id,
                'admin_name' => $admin->name,
                'name'  => $name,
                'content' => $content
            ]);
        }
    }
}

if (!function_exists('enable_transfer_point_log')) {
    function enable_transfer_point_log($user)
    {
        admin_log(\App\Models\AdminTransaction::ENABLE_CUSTOMER_TRANSFER_POINT, $user->email);
    }
}

if (!function_exists('disable_transfer_point_log')) {
    function disable_transfer_point_log($user)
    {
        admin_log(\App\Models\AdminTransaction::DISABLE_CUSTOMER_TRANSFER_POINT, $user->email);
    }
}
?>