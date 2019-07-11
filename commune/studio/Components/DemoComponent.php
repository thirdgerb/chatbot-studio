<?php


namespace Commune\Studio\Components;


use Commune\Chatbot\Framework\Component\ComponentOption;
use Commune\Demo\App\DemoOption;

class DemoComponent extends ComponentOption
{

    public static function stub(): array
    {
        return [];
    }

    protected function doBootstrap(): void
    {
        $this->dependComponent(DemoOption::class);

        $this->loadSelfRegisterByPsr4(
            "Commune\\Studio\\Components\\Demo",
            __DIR__ .'/Demo'
        );
    }



}