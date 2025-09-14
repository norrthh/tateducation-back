<?php

namespace App;

use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;

class TelegraphHandler extends WebhookHandler
{
    public function start(): void
    {
        $this->chat->message("Открыть мини приложение")->keyboard(Keyboard::make()->buttons([
            Button::make('Открыть')->webApp('https://tateducationfront.tech-tonic.ru/')
        ]))->send();
    }
}
