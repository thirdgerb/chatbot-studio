<?php


namespace Commune\Studio\Components;


use Commune\Chatbot\Framework\Component\ComponentOption;
use Commune\Studio\Components\IntentLog\IntentLogRepositoryImpl;
use Commune\Studio\Components\IntentLog\Providers\IntentLogServiceProvider;

/**
 * 用于记录 incoming message 的意图匹配情况.
 * 并允许多维度查询, 和发送消息给用户.
 *
 * @property-read string|\Closure $repository
 */
class IntentLogComponent extends ComponentOption
{
    public static function stub(): array
    {
        return [
            'repository' => IntentLogRepositoryImpl::class,
        ];
    }

    protected function doBootstrap(): void
    {
        $this->loadSelfRegisterByPsr4(
            "Commune\\Studio\\Components\\IntentLog\\Contexts\\",
            __DIR__ .'/IntentLog/Contexts/'
        );

        $this->app->registerConversationService(
            new IntentLogServiceProvider(
                $this->app->getConversationContainer(),
                $this->repository
            )
        );

    }


}