<?php

namespace Blavetstudio\Sage\WooCommerceSubscriptions;

use Roots\Acorn\ServiceProvider;

class WooCommerceSubscriptionsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('woocommerce-subscriptions', WooCommerceSubscriptions::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (defined('WC_ABSPATH')) {
            $this->bindFilters();
        }
    }

    public function bindFilters()
    {
        $woocommerce_subscriptions = $this->app['woocommerce-subscriptions'];

        add_filter('wc_get_template', [$woocommerce_subscriptions, 'template']);
    }
}
