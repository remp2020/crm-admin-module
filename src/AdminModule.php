<?php

namespace Crm\AdminModule;

use Crm\AdminModule\Components\UniversalSearchWidget\UniversalSearchWidget;
use Crm\AdminModule\Events\AdminRequestInsecureEvent;
use Crm\AdminModule\Seeders\ConfigsSeeder;
use Crm\ApplicationModule\Application\Managers\AssetsManager;
use Crm\ApplicationModule\Application\Managers\LayoutManager;
use Crm\ApplicationModule\Application\Managers\SeederManager;
use Crm\ApplicationModule\CrmModule;
use Crm\ApplicationModule\Models\Event\EventsStorage;
use Crm\ApplicationModule\Models\Menu\MenuContainerInterface;
use Crm\ApplicationModule\Models\Menu\MenuItem;
use Crm\ApplicationModule\Models\Widget\LazyWidgetManagerInterface;

class AdminModule extends CrmModule
{
    public function registerAdminMenuItems(MenuContainerInterface $menuContainer)
    {
        $mainMenu = new MenuItem('', ':Application:Admin:', 'fa fa-cog', 900, true);

        $menuItem1 = new MenuItem(
            $this->translator->translate('admin.menu.settings'),
            ':Admin:ConfigAdmin:',
            'fa fa-wrench',
            100,
            true,
        );
        $mainMenu->addChild($menuItem1);

        $menuItem2 = new MenuItem(
            $this->translator->translate('admin.menu.background_jobs'),
            ':Admin:BackgroundStatusAdmin:',
            'fa fa-sync-alt',
            200,
            true,
        );
        $mainMenu->addChild($menuItem2);

        $menuItem4 = new MenuItem(
            $this->translator->translate('admin.menu.snippets'),
            ':Admin:SnippetsAdmin:',
            'fa fa-eye-dropper',
            400,
            true,
        );
        $mainMenu->addChild($menuItem4);

        $menuItem5 = new MenuItem(
            $this->translator->translate('admin.menu.audit_log'),
            ':Admin:AuditLogAdmin:',
            'fa fa-list-ul',
            500,
            true,
        );
        $mainMenu->addChild($menuItem5);

        $menuContainer->attachMenuItem($mainMenu);
    }

    public function registerLayouts(LayoutManager $layoutManager)
    {
        $layoutManager->registerLayout('admin', realpath(__DIR__ . '/templates/@admin_layout.latte'));
    }

    public function registerAssets(AssetsManager $assetsManager)
    {
        $assetsManager->copyAssets(__DIR__ . '/assets/dist/', 'layouts/admin/dist/');
        $assetsManager->copyAssets(__DIR__ . '/assets/images/', 'layouts/admin/dist/images/module/');
    }

    public function registerSeeders(SeederManager $seederManager)
    {
        $seederManager->addSeeder($this->getInstance(ConfigsSeeder::class));
    }

    public function registerEvents(EventsStorage $eventsStorage)
    {
        $eventsStorage->register('admin-request-insecure', AdminRequestInsecureEvent::class);
    }

    public function registerLazyWidgets(LazyWidgetManagerInterface $widgetManager)
    {
        $widgetManager->registerWidget(
            'admin.after_menu',
            UniversalSearchWidget::class,
        );
    }
}
