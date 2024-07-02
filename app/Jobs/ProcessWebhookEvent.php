<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Bus\Queueable;
use App\Events\ConversationCreated;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use App\Http\Resources\NewConversationResource;

class ProcessWebhookEvent implements ShouldQueue
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
        try {

            $body = $this->bodyContent['statuses'][0];
            
            $message = Message::where('api_id', $body['id'])->first();
            
            if ($message) {
                $conversation = Conversation::find($message->conversation_id);
                
                if ($conversation) {
                    
                    if(!$conversation->uuid){
                        $conversation->update([
                            'uuid' => $body['conversation']['id'],
                            'start_date' => Carbon::createFromTimestamp($body['timestamp'])
                        ]);
                    }else{
                        $conversation->update([
                            'last_activity' => Carbon::createFromTimestamp($body['timestamp'])->format('Y-m-d H:i:s')
                        ]);
                        event(new ConversationCreated([
                            'sendAdvisor' => new NewConversationResource($conversation),
                        ]));
                    }
                    
                    $message->update([
                        'date_of_issue' => Carbon::createFromTimestamp($body['timestamp']),
                        'status' => $body['status']
                    ]);
                }
            }
        } catch (\Throwable $th) {
            Log::error('Error en el trabajo de cola: ' . $th->getMessage());
        }
    }
}
