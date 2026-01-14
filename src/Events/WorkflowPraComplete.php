<?php

namespace Assure\Workflow\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Models;

class WorkflowPraComplete
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public $workOrder;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($workOrder)
    {
        info('WorkflowPraComplete - PROCESSING');
        $this->workOrder = $workOrder;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
