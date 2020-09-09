<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Phpml\Classification\Classifier;
use Phpml\Classification\SVC;
use Phpml\ModelManager;
use Phpml\SupportVectorMachine\Kernel;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // $this->app->singleton(Classifier::class, function () {
        //     $modelPath = env('ML_FILE_PATH');

        //     if (!file_exists($modelPath)) {
        //         // return new MLPClassifier(1, [2], ['field', 'ordinaries', 'charges']);
        //         return new SVC(Kernel::LINEAR, $cost = 1000);
        //     }

        //     return (new ModelManager())->restoreFromFile($modelPath);
        // });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
