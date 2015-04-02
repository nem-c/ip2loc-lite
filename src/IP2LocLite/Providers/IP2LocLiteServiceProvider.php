<?php

namespace NemC\IP2LocLite\Providers;

use Illuminate\Support\ServiceProvider,
    Illuminate\Support\Facades\File,
    NemC\IP2LocLite\Commands\LoginCommand,
    NemC\IP2LocLite\Commands\DownloadCsvCommand,
    NemC\IP2LocLite\Commands\ImportCsvCommand;

class IP2LocLiteServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->package('nem-c/ip2loc-lite');

        $app = $this->app;
        $storagePath = $app['config']->get('ip2loc-lite::config.storagePath');
        if (File::isDirectory($storagePath) === false) {
            File::makeDirectory($storagePath, 0777);
            File::put($storagePath . '/.gitignore', "*\n!.gitignore\n!cookies\n!downloads");
        }
        if (File::isDirectory($storagePath . '/cookies') === false) {
            File::makeDirectory($storagePath . '/cookies', 0777);
            File::put($storagePath . '/cookies/.gitignore', "*\n!.gitignore");
        }
        if (File::isDirectory($storagePath . '/downloads') === false) {
            File::makeDirectory($storagePath . '/downloads', 0777);
            File::put($storagePath . '/downloads/.gitignore', "*\n!.gitignore");
        }
    }

    public function register()
    {
        $this->registerLoginCommand();
        $this->registerDownloadCsv();
        $this->registerImportCsv();
    }

    protected function registerLoginCommand()
    {
        $this->app['nemc_ip2loclite_login'] = $this->app->share(function ($app) {
            return new LoginCommand(
                $app->make('NemC\IP2LocLite\Services\IP2LocLiteService')
            );
        });
        $this->commands('nemc_ip2loclite_login');
    }

    protected function registerDownloadCsv()
    {
        $this->app['nemc_ip2loclite_download_csv'] = $this->app->share(function ($app) {
            return new DownloadCsvCommand(
                $app->make('NemC\IP2LocLite\Services\IP2LocLiteService')
            );
        });
        $this->commands('nemc_ip2loclite_download_csv');
    }

    protected function registerImportCsv()
    {
        $this->app['nemc_ip2loclite_import_csv'] = $this->app->share(function ($app) {
            return new ImportCsvCommand(
                $app->make('NemC\IP2LocLite\Services\IP2LocLiteService'),
                $app->make('NemC\IP2LocLite\Repositories\IP2LocRepository'),
                $app->make('ZipArchive')
            );
        });
        $this->commands('nemc_ip2loclite_import_csv');
    }
}