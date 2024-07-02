<?php

namespace App\Jobs;

use App\Models\Contact;
use App\Models\Conversation;
use Illuminate\Bus\Queueable;
use App\Events\ConversationCreated;
use App\Http\Controllers\WspController;
use App\Http\Resources\NewConversationResource;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class ProcessReceivedMessageWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $message;

    /**
     * Create a new job instance.
     */
    public function __construct($message)
    {
        $this->message = $message['message'] ?? null;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // Obtener el contacto con ID 24 (o el contacto correspondiente)
        $contact = Contact::find(24);
        if (!$contact) {
            Log::error("Contacto con ID 24 no encontrado");
            return;
        }

        // Verificar si existe una conversación abierta para el contacto
        $lastConversation = Conversation::where('contact_id', $contact->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastConversation && $lastConversation->status == 'open') {
            // Acción basada en el mensaje recibido
            $wspController = new WspController();

            // Definir las palabras clave y las respuestas asociadas
            $keywords = [
                'levantamiento catastral' => '🗺️📏 El levantamiento catastral es el proceso de medición y documentación detallada de los terrenos, incluyendo la identificación de sus límites y características.',
                'elaboración cartográfica' => '🗺️📐 La elaboración cartográfica de predios rústicos incluye la creación de mapas detallados que representan la geografía y los límites de los terrenos rústicos.',
                'adjudicación de tierras' => '🌍📜 La adjudicación de tierras se lleva a cabo mediante un proceso administrativo que asigna terrenos a individuos o comunidades, basándose en la legislación vigente y los estudios catastrales.',
                'saneamiento físico legal' => '🏠⚖️ El saneamiento físico legal de la propiedad agraria es el proceso de regularización y formalización de los derechos de propiedad sobre tierras agrícolas, asegurando que estén debidamente registradas y libres de conflictos legales.',
                'demarcación del territorio' => '📍🌿 La demarcación del territorio de comunidades nativas implica el establecimiento de los límites territoriales de las comunidades, basado en estudios técnicos y en consulta con las comunidades afectadas.',
                'titulación del territorio' => '📜🏞️ La titulación del territorio de comunidades nativas es el proceso de otorgar títulos de propiedad a las comunidades nativas sobre sus tierras ancestrales, formalizando su posesión y uso.',
                'evaluación de proyectos de inversión' => '📈💡 La evaluación de proyectos de inversión implica revisar y analizar las propuestas de inversión en el sector agrícola para determinar su viabilidad y impacto.',
                'ADELs' => '🏢🌱 Las ADELs son las Agencias de Desarrollo Económico Local. Sus acciones se supervisan para asegurar que cumplan con sus objetivos de promover el desarrollo económico y social en sus áreas de influencia.',
                'rectificación de áreas y linderos' => '📐🗂️ Para solicitar la rectificación de áreas y linderos, debe presentar una solicitud formal al área de catastro, la cual se encargará de revisar y corregir los datos.',
                'diagnóstico de catastro' => '📝💼 El diagnóstico por parte del área de catastro cuesta 27 soles según el TUPA. Si es procedente, pasa al área de TUPA para su gestión.',
                'evaluación de un expediente' => '📋🕵️‍♂️ La evaluación de un expediente cuesta 27 soles.',
                'solicitar una inspección' => '🔍🏘️ Para solicitar una inspección, debe presentar una solicitud en la mesa de partes, especificando los detalles del predio y el propósito de la inspección.',
                'saneamiento de tierras' => '🏞️🔏 El saneamiento de tierras es el proceso de regularización y formalización de los derechos de propiedad, asegurando que las tierras estén legalmente reconocidas y registradas.',
                'inmatriculación' => '📜🏡 La inmatriculación es el proceso de inscripción inicial de una propiedad en el registro de tierras, formalizando su existencia legal.',
                'tracto sucesivo' => '🔄📖 El tracto sucesivo es el registro continuo y actualizado de todas las transferencias y cambios de propiedad de un terreno, asegurando que todos los actos de disposición estén debidamente documentados.',
                'prescripción adquisitiva' => '⏳🏠 La prescripción adquisitiva es un mecanismo legal mediante el cual una persona puede adquirir la propiedad de un terreno tras poseerlo de manera continua y pacífica durante un período determinado por la ley.',
                'información de coordenadas' => '📍🗺️ Para solicitar información de coordenadas, necesita presentar la solicitud que provee la mesa de partes, el plano o punto de coordenadas, y una copia del DNI.',
                'copias fedateadas' => '📑✔️ Para solicitar copias fedateadas, debe presentar la solicitud que le provee la mesa de partes y una copia del DNI.',
                'número de expediente del GSTRAMITE' => '📂🔢 Puede acceder al número de expediente del GSTRAMITE consultando directamente en la plataforma o solicitándolo en la mesa de partes.',
                'código con referencia al área de catastro' => '🔢🗺️ El código con referencia al área de catastro se asigna una vez que el terreno ha sido debidamente registrado y catalogado por el área de catastro.',
                'actualización de datos catastrales' => '📋🔄 Para solicitar la actualización de sus datos catastrales, debe presentar una solicitud formal en la mesa de partes, junto con la documentación necesaria que respalde los cambios requeridos.',
                'inscripción en el registro de comercializadores' => '📝💰 El costo del derecho de tramitación para la inscripción en el registro de comercializadores es S/ 172.90.',
                'copia simple en formato A4' => '📝📄 Una copia simple en formato A4 cuesta S/ 0.10.',
                'copia simple en formato A3' => '📝📄 Una copia simple en formato A3 cuesta S/ 2.00.',
                'copia simple en formato A2' => '📝📄 Una copia simple en formato A2 cuesta S/ 5.00.',
                'copia simple en formato A1' => '📝📄 Una copia simple en formato A1 cuesta S/ 6.00.',
                'copia simple en formato A0' => '📝📄 Una copia simple en formato A0 cuesta S/ 9.00.',
                'tramitación o evaluación de expediente' => '🗂️💼 El costo del derecho de tramitación o evaluación de expediente es S/ 26.80.',
                'visación de planos y memoria descriptiva de predios rurales para procesos judiciales' => '📑⚖️ El precio para la visación de planos y memoria descriptiva de predios rurales para procesos judiciales es S/ 86.40.',
                'inspección de campo' => '🏞️🔍 El costo del pago por concepto de "Inspección de campo" varía por rango y distancia.',
                'asignación de código de referencia catastral y expedición de certificado de información catastral' => '📋📜 La asignación de código de referencia catastral y expedición de certificado de información catastral tiene un costo de S/ 108.00.',
                'autorización de transporte' => '🚚📜 El precio de la autorización de transporte es S/ 15.40.',
                'inspección in situ para verificar el cumplimiento de los requisitos específicos' => '🔍🏡 La inspección in situ para verificar el cumplimiento de los requisitos específicos cuesta S/ 240.70.',
                'inspección de campo para predios rurales menores de 1.50 Has' => '🏞️🔍 El monto de la inspección de campo para predios rurales menores de 1.50 Has es S/ 1106.00.',
                'inspección de campo para predios rurales desde 1.51 Has. hasta 3 Has' => '🏞️🔍 El monto de la inspección de campo para predios rurales desde 1.51 Has. hasta 3 Has es S/ 279.00.',
                'inspección de campo para predios rurales desde 3.1 Has. hasta 5 Has' => '🏞️🔍 El monto de la inspección de campo para predios rurales desde 3.1 Has. hasta 5 Has es S/ 284.00.',
                'inspección de campo para predios rurales desde 5.1 Has. hasta 10 Has' => '🏞️🔍 El monto de la inspección de campo para predios rurales desde 5.1 Has. hasta 10 Has es S/ 289.00.',
                'inspección de campo para predios rurales desde 10.1 Has. hasta 20 Has' => '🏞️🔍 El monto de la inspección de campo para predios rurales desde 10.1 Has. hasta 20 Has es S/ 351.00.',
                'inspección de campo para predios rurales desde 20.1 Has. hasta 50 Has' => '🏞️🔍 El monto de la inspección de campo para predios rurales desde 20.1 Has. hasta 50 Has es S/ 377.00.',
                'inspección de campo para predios rurales desde 50.1 Has. hasta 100 Has' => '🏞️🔍 El monto de la inspección de campo para predios rurales desde 50.1 Has. hasta 100 Has es S/ 387.00.',
                'inspección de campo para predios rurales desde 100.1 Has. hasta 300 Has' => '🏞️🔍 El monto de la inspección de campo para predios rurales desde 100.1 Has. hasta 300 Has es S/ 408.00.',
                'inspección de campo para predios rurales desde 300.1 Has. hasta 500 Has' => '🏞️🔍 El monto de la inspección de campo para predios rurales desde 300.1 Has. hasta 500 Has es S/ 685.00.',
                'inspección de campo para predios rurales desde 500.1 Has. hasta 2000 Has' => '🏞️🔍 El monto de la inspección de campo para predios rurales desde 500.1 Has. hasta 2000 Has es S/ 730.00.',
                'inspección de campo para predios rurales desde 2000.1 Has. a más' => '🏞️🔍El monto de la inspección de campo para predios rurales desde 2000.1 Has. a más es S/ 798.00.',
                'DRASAM' => '🌿 La Dirección Regional de Agricultura San Martín (DRASAM) es el órgano de línea de la Gerencia Regional de Desarrollo Económico del Gobierno Regional de San Martín, y su ámbito territorial y funcional es el mismo que corresponde al Gobierno Regional de San Martín.',
                'funciones generales de la DRASAM' => '📋 Algunas funciones generales de la DRASAM incluyen formular, proponer, dirigir, supervisar y evaluar políticas, planes, programas, proyectos y servicios públicos regionales en materia sectorial bajo su competencia, además de ejercer autoridad sectorial regional en las funciones de su competencia transferidas al Gobierno Regional por el Gobierno Nacional (MINAGRI).',
                'costos asociados con los servicios de alquiler de GPS' => '💼 Por 01 día: S/ 250.00
Por 02 - 03 días: S/ 350.00
Por 04 - 05 días: S/ 450.00.',
                'costo del servicio de reparación de maquinaria agrícola' => '🔧 El costo del servicio de reparación de maquinaria agrícola, específicamente para la reparación de motor, sistema de transmisión, sistema eléctrico y rodamiento de tractores agrícolas es S/ 12,000.00.',
                'ubicada la DRASAM' => '📍 La Dirección Regional de Agricultura San Martín (DRASAM) cuenta con dos sedes. La Sede Central está ubicada en Jr. Ángel Delgado Morey S/N, San Martín - Tarapoto, Perú. Y la Sede MOYOBAMBA se encuentra en Alonso de Alvarado 486, San Martín - Moyobamba, Perú.',
                'Sede Central TARAPOTO de la Dirección Regional de Agricultura San Martín' => '🏢 La Sede Central TARAPOTO se encuentra en Jr. Ángel Delgado Morey S/N, San Martín - Tarapoto, Perú. (Referencia: Altura Cdra 15 Jr. Leguia).',
                'número de teléfono de la Sede Central TARAPOTO' => '📞 El número de teléfono es 042-351114.',
                'horario de atención de la Sede Central TARAPOTO' => '🕒 El horario de atención es de lunes a viernes:
🕖 7:13 a. m. - 11:00 p. m.
🕑 2:17 p. m. - 11:00 p. m.',
                'correo electrónico de contacto para la Sede Central TARAPOTO' => '✉️ El correo electrónico es drasam@drasam.gob.pe.',
                'ubicada la Sede MOYOBAMBA de la Dirección Regional de Agricultura San Martín' => '🏢 La Sede MOYOBAMBA se encuentra en Alonso de Alvarado 486, San Martín - Moyobamba, Perú.',
                'número de teléfono de la Sede MOYOBAMBA' => '📞 El número de teléfono es 042-561595.',
                'horario de atención de la Sede MOYOBAMBA' => '🕒 El horario de atención es de lunes a viernes de 7:13 a. m. a 11:00 p. m. y todos los días de 2:17 p. m. a 11:00 p. m.',
                'correo electrónico de contacto para la Sede MOYOBAMBA' => '✉️ El correo electrónico es drasam@drasam.gob.pe.',
                'DRASAM tiene página de Facebook' => '📘 ¡Por supuesto! Puedes visitar nuestra página oficial de Facebook en el siguiente enlace: https://www.facebook.com/drasamoficial. Si tienes alguna otra consulta, estaré encantado de ayudarte.'
            ];
            // Verificar si el mensaje contiene las palabras clave "comunicarme" o "oficina"
            if (strpos($this->message, 'comunicarme') !== false || strpos($this->message, 'oficina') !== false) {

                $optionsMessage = "Escoge una de las siguientes opciones";
                $interactiveMessage = $wspController->struct_messages_list_webhook($optionsMessage, $contact->num_phone);

                // Enviar el mensaje estructurado
                $messageResponse = $wspController->envia($interactiveMessage);

                // Guardar el mensaje utilizando $messageResponse
                $wspController->createMessageWelcome($lastConversation, $messageResponse, [
                    'type' => 'interactive',
                    'interactive' => $interactiveMessage, // Aquí el cuerpo del mensaje interactivo completo
                    'timestamp' => now()->timestamp
                ], $contact);

                // Llamar a sendMessageAndLog
                event(new ConversationCreated(new NewConversationResource($lastConversation)));
            } elseif (strpos($this->message, 'gracias') !== false || strpos($this->message, 'muchas gracias') !== false) {
                // Verificar si el mensaje contiene las palabras clave "gracias" o "muchas gracias"

                $optionsMessage = "Gracias por usar nuestro servicio. Nos complace pedirle una valoración de nuestro servicio";
                $interactiveMessage = $wspController->struct_messages_list_satisfaction_customers($optionsMessage, $contact->num_phone);

                // Enviar el mensaje estructurado
                $messageResponse = $wspController->envia($interactiveMessage);

                // Guardar el mensaje utilizando $messageResponse
                $wspController->createMessageWelcome($lastConversation, $messageResponse, [
                    'type' => 'interactive',
                    'interactive' => $interactiveMessage, // Aquí el cuerpo del mensaje interactivo completo
                    'timestamp' => now()->timestamp
                ], $contact);

                // Llamar a sendMessageAndLog
                event(new ConversationCreated(new NewConversationResource($lastConversation)));
            } else {
                // Buscar coincidencias parciales en las palabras clave
                $responseText = null;
                $maxSimilarity = 0;
                foreach ($keywords as $keyword => $answer) {
                    similar_text($this->message, $keyword, $percent);
                    if ($percent > $maxSimilarity) {
                        $maxSimilarity = $percent;
                        $responseText = $answer;
                    }
                }

                // Si no hay coincidencias significativas, usar el mensaje predeterminado
                $validMessage = preg_match('/[A-Za-z]/', $this->message); // Verifica si contiene letras

                if ($maxSimilarity < 30 || !$validMessage) { // Puedes ajustar este umbral según sea necesario
                    $responseText = "Por favor, escriba alguna consulta o duda acorde. No podemos procesar caracteres especiales ni palabras que no tengan coincidencia 📄.";
                }

                $structuredMessage = $wspController->struct_message($responseText, $contact->num_phone);

                $messageResponse = $wspController->envia($structuredMessage);
                $wspController->createMessageWelcome($lastConversation, $messageResponse, $structuredMessage, $contact);

                // Ejemplo de evento de conversación creada
                event(new ConversationCreated(new NewConversationResource($lastConversation)));
            }
        }
    }
}
