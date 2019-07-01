<?php


namespace Commune\Studio;


use Commune\Chatbot\Framework\Component\ComponentOption;

/**
 * @property-read string $namespace
 * @property-read string $root
 * @property-read string[] $preload
 */
class StudioLoader extends ComponentOption
{

    public static function stub(): array
    {
        return [
            'namespace' => "Commune\\Studio\\",
            'root' => realpath(__DIR__ . '/../') . '/',
            'preload' => [
                'Intents',
                'Contexts',
                'Memories',
            ]
        ];
    }

    protected function doBootstrap(): void
    {
        foreach ($this->preload as $name) {
            $this->loadSelfRegisterByPsr4(
                $this->namespace . $name,
                $this->root . $name
            );
        }
    }


}