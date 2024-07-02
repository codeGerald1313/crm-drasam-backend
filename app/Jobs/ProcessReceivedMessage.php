<?php

namespace App\Jobs;

use App\Models\Contact;
use App\Models\Conexion;
use App\Models\Conversation;
use Illuminate\Bus\Queueable;
use App\Events\ConversationCreated;
use Illuminate\Queue\SerializesModels;
use App\Http\Controllers\WspController;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Http\Resources\NewConversationResource;

class ProcessReceivedMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $bodyContent;

    /**
     * Create a new job instance.
     */
    public function __construct($bodyContent)
    {
        $this->bodyContent = $bodyContent;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //SAVE CONTACT
        $DataDontact = $this->bodyContent['contacts'][0];
                
        $contact = Contact::where('num_phone', $DataDontact['wa_id'])->first();
       
        if(!$contact) {
            $contact = Contact::create([
                'name' => $DataDontact['profile']['name'],
                'document' => "00000000",
                'email' => 'mail@mail.com',
                'num_phone' => $DataDontact['wa_id'],
                'status' => 1,
                'student' => 0
            ]);
        }

        $lastConversation = Conversation::where('contact_id', $contact->id)
        ->orderBy('created_at', 'desc')
        ->first();

        $conexion = Conexion::where('status', 1)->first();
        
        $wspController = new WspController();

        if(!$lastConversation)
        {
            $conversation = $wspController->saveMessage($contact, $this->bodyContent['messages'][0]);

            if($conversation->status_bot == 1 && $conexion->status_bot == 1){ 
                
                $text = $conexion->welcome;

                $response_message = $wspController->struct_Message($text,$contact->num_phone);
                $messageResponse = $wspController->envia($response_message);

                $wspController->messageWelcome($conversation,$messageResponse,$response_message);
            }
            event(new ConversationCreated(new NewConversationResource($conversation)));
            
        }
        elseif ($lastConversation->status == 'open') 
        {
            $wspController->saveMessage($lastConversation, $this->bodyContent['messages'][0]);
            event(new ConversationCreated(new NewConversationResource($lastConversation)));

        }elseif ($lastConversation->status == 'close') 
        {
            if($lastConversation->status_bot == 1 && $conexion->status_bot == 1){
                
                $text = $conexion->welcome;

                $response_message = $wspController->struct_Message($text,$contact->num_phone);
                $messageResponse = $wspController->envia($response_message);
                
                $wspController->messageWelcome($lastConversation,$messageResponse,$response_message);
            } 
            $wspController->reopenAndSaveMessage($lastConversation, $this->bodyContent['messages'][0]);

            event(new ConversationCreated(new NewConversationResource($lastConversation)));
        }
    }
}
