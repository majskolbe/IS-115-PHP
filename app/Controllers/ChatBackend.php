<?php
require_once __DIR__ . '/ChatController.php';
require_once __DIR__ . '/../../config/config.php';

/**
 * Klasse som håndterer chat-logikken.
 * Kan brukes både som modul i systemet (via handleMessage)
 * og som eget endepunkt som tar imot HTTP-forespørsler og svarer med JSON.
 */
class ChatBackend
{
    private ChatController $controller;

    public function __construct(?ChatController $controller = null)
    {
        // Oppretter en ChatController hvis ingen er sendt inn
        $this->controller = $controller ?? new ChatController();
    }

    /**
     * Tar imot en melding og gir tilbake et svar i et array.
     *
     * @param string $message Brukerens melding
     * @return array Med enten 'reply' (svar) eller 'error' (feilmelding)
     */

    public function handleMessage(string $message): array
    {
        // Fjerner unødvendig mellomrom
        $message = trim($message);

        // Returnerer feilmelding hvis input er tom
        if ($message === '') {
            return ['reply' => 'Meldingen kan ikke være tom.'];
        }

        // Sender meldingen til ChatController og får svar
        $reply = $this->controller->handleUserMessage($message);
        return ['reply' => $reply];
    }

    /**
     * Hjelpefunksjon for å validere rå JSON‑input.
     * Returnerer null hvis alt er gyldig, ellers en feilmelding.
     */
    public static function validateInput(array $input): ?string
    {
        if (!isset($input['message'])) return 'Mangler feltet "message" i forespørselen.';
        if (!is_string($input['message'])) return 'Feltet "message" må være en tekst.';
        return null;
    }
}

// Bootstrap: kjører kun I/O hvis denne filen faktisk er forespurt direkte
// (bevarer objektorientert bruk når den inkluderes andre steder)
if (php_sapi_name() !== 'cli' && realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'])) {
    header('Content-Type: application/json; charset=utf-8');
    try {
        // Tillater kun POST‑forespørsler
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Kun POST er tillatt.']);
            exit;
        }

        // Leser rå JSON‑data fra forespørselen
        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true);

        // Sjekker at JSON ble dekodet til et array
        if (!is_array($input)) {
            echo json_encode(['error' => 'Ugyldig JSON i forespørsel.']);
            exit;
        }

        // Validerer at input inneholder feltet "message"
        $validation = ChatBackend::validateInput($input);
        if ($validation !== null) {
            echo json_encode(['error' => $validation]);
            exit;
        }

        // Oppretter backend og håndterer meldingen
        $backend = new ChatBackend();
        $result = $backend->handleMessage($input['message']);

        // Returnerer resultatet som JSON
        echo json_encode($result);
        exit;
    } catch (Throwable $e) {
        // Fanger opp uventede feil og returnerer 500
        http_response_code(500);
        echo json_encode(['error' => 'Serverfeil: ' . $e->getMessage()]);
        exit;
    }
}
