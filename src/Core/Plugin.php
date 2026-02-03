<?php

namespace SweetPortofolio\Core;

class Plugin
{
    public function run()
    {
        $this->load_admin();
        $this->load_api();
        $this->load_frontend();
    }

    private function load_admin()
    {
        // Admin Settings
        if (class_exists('\SweetPortofolio\Admin\Settings')) {
            $settings = new \SweetPortofolio\Admin\Settings();
            $settings->register();
        }
        
        // Admin Assets
        if (class_exists('\SweetPortofolio\Admin\Enqueue')) {
            new \SweetPortofolio\Admin\Enqueue();
        }
    }

    private function load_api()
    {
        // API Routes
        if (class_exists('\SweetPortofolio\Api\PortofolioController')) {
            new \SweetPortofolio\Api\PortofolioController();
        }
    }

    private function load_frontend()
    {
        // Frontend Assets
        if (class_exists('\SweetPortofolio\Frontend\Enqueue')) {
            new \SweetPortofolio\Frontend\Enqueue();
        }

        // Templates
        if (class_exists('\SweetPortofolio\Core\TemplateLoader')) {
            new \SweetPortofolio\Core\TemplateLoader();
        }

        // Shortcodes
        if (class_exists('\SweetPortofolio\Frontend\Shortcode')) {
            new \SweetPortofolio\Frontend\Shortcode();
        }
    }
}
