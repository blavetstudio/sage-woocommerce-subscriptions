<?php

namespace Blavetstudio\Sage\WooCommerceSubscriptions;

use Illuminate\Contracts\Container\Container as ContainerContract;
use Roots\Acorn\Sage\ViewFinder;
use Roots\Acorn\View\FileViewFinder;
use Illuminate\Support\Str;

use function Roots\view;

class WooCommerceSubscriptions
{
    public function __construct(
        ViewFinder $sageFinder,
        FileViewFinder $fileFinder,
        ContainerContract $app
    ) {
        $this->app = $app;
        $this->fileFinder = $fileFinder;
        $this->sageFinder = $sageFinder;
    }

    /**
     * Support blade templates for the main template include.
     */
    public function templateInclude(string $template): string
    {
        if (!$this->isWooCommerceSubscriptionsTemplate($template)) {
            return $template;
        }
        return $this->locateThemeTemplate($template) ?: $template;
    }

    /**
     * Support blade templates for the woocommerce comments/reviews.
     */
    public function reviewsTemplate(string $template): string
    {
        if (!$this->isWooCommerceSubscriptionsTemplate($template)) {
            return $template;
        }

        return $this->template($template);
    }

    /**
     * Filter a template path, taking into account theme templates and creating
     * blade loaders as needed.
     */
    public function template(string $template): string
    {
        // Locate any matching template within the theme.
        $themeTemplate = $this->locateThemeTemplate($template);
        if (!$themeTemplate) {
            return $template;
        }

        // TODO: Return filename for status screen.
        // WooCommerce Subscriptions doesn't filter the template files locations as WooCommerce does, so this code is not used
        // woocommerce-subscriptions/includes/admin/class-wcs-admin-system-status.php:163
        // if (
        //     is_admin() &&
        //     !wp_doing_ajax() &&
        //     get_current_screen() &&
        //     get_current_screen()->id === 'woocommerce_page_wc-status'
        // ) {
        //     return $themeTemplate;
        // }

        // Include directly unless it's a blade file.
        if (!Str::endsWith($themeTemplate, '.blade.php')) {
            return $themeTemplate;
        }

        // We have a template, create a loader file and return it's path.
        return view(
            $this->fileFinder->getPossibleViewNameFromPath(realpath($themeTemplate))
        )->makeLoader();
    }

    /**
     * Get WooCommerce Subscriptions Path
     */
    protected function getWooCommerceSubscriptionsPath(): string
    {
        // Sadly there is no constant defined for WC Subscriptions :S
        $wc_subscriptions_path = trailingslashit(untrailingslashit(\WC_ABSPATH) . '-subscriptions');
        return $wc_subscriptions_path;
    }

    /**
     * Check if template is a WooCommerce Subscriptions template.
     */
    protected function isWooCommerceSubscriptionsTemplate(string $template): bool
    {
        return strpos($template, $this->getWooCommerceSubscriptionsPath()) !== false;
    }

    /**
     * Locate the theme's WooCommerce Subscriptions blade template when available in the WooCommerce template path.
     */
    protected function locateThemeTemplate(string $template): string
    {
        $themeTemplate = WC()->template_path() . str_replace($this->getWooCommerceSubscriptionsPath() . 'templates/', '', $template);
        return locate_template($this->sageFinder->locate($themeTemplate));
    }
}
