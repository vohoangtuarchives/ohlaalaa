<?php

namespace App\Providers;

use App;
use Session;
use App\Models\User;
use App\Models\Category;
use App\Classes\HTDPhoto;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\LengthAwarePaginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        $admin_lang = DB::table('admin_languages')->where('is_default','=',1)->first();
        App::setlocale($admin_lang->name);
        // User::chekValidation();


        if (!Session::has('df_currency')) {
            $curr = DB::table('currencies')->where('is_default','=',1)->first();
            Session::put('df_currency' , $curr);
        }

        ini_set('memory_limit', '512M');
        ini_set('xdebug.max_nesting_level', 50000);

        $app_url = config('app.url');
        \URL::forceRootUrl($app_url);
        if (\Str::contains($app_url, 'https://')) {
            \URL::forceScheme('https');
        }

        Schema::defaultStringLength(191);

        view()->composer('*',function($settings){
            $settings->with('gs', cache()->remember('generalsettings', now()->addDay(), function () {
                return DB::table('generalsettings')->first();
            }));

            $settings->with('seo', cache()->remember('seotools', now()->addDay(), function () {
                return DB::table('seotools')->first();
            }));

            $settings->with('socialsetting', cache()->remember('socialsettings', now()->addDay(), function () {
                return DB::table('socialsettings')->first();
            }));

            $settings->with('categories', cache()->remember('categories', now()->addDay(), function () {
                return Category::with('subs')->get();
            }));

            $htd_pho = new HTDPhoto;
            $settings->with('htd_photo', $htd_pho);

            if (Session::has('language')){
                $data = cache()->remember('session_language', now()->addDay(), function () {
                    return DB::table('languages')->find(Session::get('language'));
                });
                $data_results = file_get_contents(public_path().'/assets/languages/'.$data->file);
                $lang = json_decode($data_results);

                $settings->with('langg', $lang);
            }
            else{
                $data = cache()->remember('default_language', now()->addDay(), function () {
                    return DB::table('languages')->where('is_default','=',1)->first();
                });
                $data_results = file_get_contents(public_path().'/assets/languages/'.$data->file);
                $lang = json_decode($data_results);
                $settings->with('langg', $lang);
            }

            if (!Session::has('popup'))
            {
                $settings->with('visited', 1);
            }

            Session::put('popup' , 1);

        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (env('APP_DEBUG')) {
            $this->app->register(\Barryvdh\Debugbar\ServiceProvider::class);
        }

        Collection::macro('paginate', function($perPage, $total = null, $page = null, $pageName = 'page') {
            $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);
            return new LengthAwarePaginator(
                $this->forPage($page, $perPage),
                $total ?: $this->count(),
                $perPage,
                $page,
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'pageName' => $pageName,
                ]
            );
        });

    }
}
