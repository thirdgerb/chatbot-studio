<?php


namespace Commune\Studio\Components\Demo\Guide;


use Commune\Chatbot\App\Callables\Actions\Redirector;
use Commune\Chatbot\App\Contexts\ScriptDef;
use Commune\Chatbot\App\Messages\QA\Choice;
use Commune\Chatbot\App\Messages\QA\Confirmation;
use Commune\Chatbot\OOHost\Context\Hearing;
use Commune\Chatbot\OOHost\Context\Stage;
use Commune\Chatbot\OOHost\Dialogue\Dialog;
use Commune\Chatbot\OOHost\Directing\Navigator;
use Commune\Studio\Components\Demo\Supports\ScriptTrait;

class GuideScript extends ScriptDef
{
    use ScriptTrait;

    const DESCRIPTION = 'demo 使用指引';


    public function __onFinal(Stage $stage): Navigator
    {
        return $stage
            ->buildTalk()
            ->fulfill();
    }

    public function __hearing(Hearing $hearing): void
    {
        $hearing
            ->is('exit', [Redirector::class, 'cancel'])
            ->isAnyIntent();

    }

    public function getSlots(): array
    {
        return [];
    }


    public static function getScripts(): array
    {
        return [
            'use_empty' => <<<EOF
开始使用指引的内容. 输入"exit" 可以随时退出.

首先, 请尝试输入"." 来表示一个空信息并且继续
EOF
            ,
            'why_empty' => <<<EOF
谢谢. 多轮对话在许多场景里只需要回复一个空消息就可以继续. 

但一些IM, 比如微信, 并不支持输入空消息, 所以用特殊字符代替.
EOF
            ,
            'use_command' => <<<EOF
本项目支持类似 shell 的命令行操作 (参考了 symfony 的实现). 可输入 "#help" 查看可用命令.

常用导航类命令:

  #back          : 回到上一轮对话
  #quit          : 退出当前会话
  #cancel        : 退出当前语境
  #repeat        : 重复当前语境.
  #restart       : 重启当前语境
EOF
,
            'about_nlu' => <<<EOF
本项目加入了自然语言识别的功能单元(NLU). 使用了开源项目 Rasa 作为中间件.

您可以在多轮对话的相关选项中查看测试用例.
EOF
,
            'test_choose' => 'testChoose',
            'test_confirm' => 'testConfirm',
            'is_testing' => <<<EOF
目前项目还在测试中, 由于作者精力有限, 没有仔细琢磨测试用例和文本.同样可能出现任何bug, 敬请谅解. 

感谢您的协助!
EOF
            ,
        ];
    }


    public function testChoose(Stage $stage) : Navigator
    {
        return $stage->buildTalk()
            ->info(
                <<<EOF
本项目在多轮对话中大量使用了"选择"功能. 用于系统主动的多轮对话.

这时输入数字序号就能选中选项. 也可以输入 "." 表示选择()内的默认值. 
EOF

            )
            ->askChoose(
                "请测试选择一个选项.",
                ['case 1', 'case 2' , 'case 3'],
                2
            )
            ->wait()
            ->hearing()
            ->isAnswer(function(Dialog $dialog, Choice $choice){

                $id = $choice->getChoice();
                $result = $choice->toResult();

                $dialog
                    ->say([
                        'id' => $id,
                        'result' => $result,
                    ])
                    ->info(' 选择了 %id%, 内容是"%result%".');

                return $dialog->goStage('askContinue');
            })
            ->isAnyIntent()
            ->end(function(Dialog $dialog){
                $dialog->say()->error("没有命中任何选项, 输入数字可以继续");
                return $dialog->repeat();
            });
    }

    public function testConfirm(Stage $stage) : Navigator
    {
        return $stage->buildTalk()
            ->info(
                <<<EOF
有时候机器人会请您确认某个信息. 会提示 [yes|no] (yes)

这是表示您可以输入 yes 或 no 表达您的选择. 也可以输入首字母 y 或 n , 1 或 0, 同样能命中选项.

输入 "." 表示同意默认值. 

EOF
            )
            ->askConfirm(
                "请测试是否判断. 苹果是水果吗? ",
                true,
                'yes',
                'no'
            )
            ->wait()
            ->hearing()
            ->isAnswer(function(Dialog $dialog, Confirmation $choice){

                $id = $choice->getChoice();
                $result = $id ? 'yes' : 'no';
                $dialog
                    ->say([
                        'result' => $result,
                    ])
                    ->info(' 选择了 %result% .');

                return $dialog->goStage('askContinue');
            })
            ->end(function(Dialog $dialog){
                $dialog->say()->error("没有命中任何选项");
                return $dialog->repeat();
            });
    }

    public function __onAskContinue(Stage $stage) : Navigator
    {
        return $stage->buildTalk()
            ->info("输入任意内容继续:")
            ->wait()
            ->next();
    }


}