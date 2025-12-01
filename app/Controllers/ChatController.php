<?php

require_once __DIR__ . '/../Models/ChatModel.php';
require_once __DIR__ . '/../Models/KassalappAPIModel.php';
require_once __DIR__ . '/../Models/EANLookupModel.php';
require_once __DIR__ . '/../Services/ChatService.php';
require_once __DIR__ . '/../Views/ChatResponseView.php';
require_once __DIR__ . '/../Utils/MessageUtils.php';

/*
Klasse med ansvar for å binde logikken med frontend.
Tar imot melding fra bruker, normaliserer og trekker ut EAN.
Sender melding og intent videre til ChatService og sender
resultatet videre til ChatResponseView.
*/

class ChatController {
    private $service;
    private $model;

    public function __construct() {
        $this->model = new ChatModel();
        $api = new KassalappAPIModel(KASSALAPP_API_KEY);
        $lookup = new EANLookupModel($api);
        $this->service = new ChatService($this->model, $lookup);
    }

    //hånterer melding fra bruker
    public function handleUserMessage($message) {
        
        $message = MessageUtils::normalize($message);
        $intent = $this->model->detectIntentByPattern($message);
        $ean = MessageUtils::extractEAN($message);

        $result = $this->service->processIntent($intent, $message, $ean);

        error_log("Intent: $intent");
        error_log("EAN: $ean");
        error_log("Result: " . print_r($result, true));

        if (!is_array($result)) {
            $result = ['reply' => "Beklager, jeg forstod ikke helt. Prøv gjerne å sende en EAN-kode."];
        }

        return ChatResponseView::render($intent, $result);
    }


}
