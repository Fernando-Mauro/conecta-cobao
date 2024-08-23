<?php

namespace App\Providers;

use App\Features\WhatsApp\Data\Repositories\WhatsAppRepositoryImpl;
use App\Features\WhatsApp\Domain\Repositories\WhatsAppRepository;
use Illuminate\Support\ServiceProvider;
class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(WhatsAppRepository::class, WhatsAppRepositoryImpl::class);
        // Continúa con las demás vinculaciones...
    }
}
