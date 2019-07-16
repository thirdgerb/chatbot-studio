<?php

return [
    'introduce' =>
<<<EOF
commune/chatbot 是一个"工程化的多轮对话交互框架"

可搭建基于文字通讯, 或语音通讯的对话机器人.

您在这儿可以通过多轮对话, 了解本项目的情况, 或体验测试用例.
EOF
    ,

    'startConversation' =>
<<<EOF
您好, %user.name%. 欢迎您的第%times%次访问. 

这里是"%self.desc%" %self.project% 项目的demo. 

您可以在此了解本项目, 或尝试测试一下. 
EOF
    ,

    'whatIsConversation_1' => <<<EOF
自然语言对话机器人, 我个人(非专业)理解: 现阶段有两大工程, 其一是语意理解, 其二是对话管理.




EOF
    ,

    'nlu' => include __DIR__ .'/nlu.php',

    'conversation' => include __DIR__ .'/conversation.php',

];
