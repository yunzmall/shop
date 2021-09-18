<?php


namespace app\framework\Events;


use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class Dispatcher extends \Illuminate\Events\Dispatcher
{
    /**
     * @param $event
     * @param $id
     * @param array $payload
     * @return array
     * @throws \ReflectionException
     */
    public function safeFire($event, $id, $payload = [])
    {
        // When the given "event" is actually an object we will assume it is an event
        // object and use the class as the event name and this event itself as the
        // payload to the handler, which makes object based events quite simple.


        [$event, $payload] = $this->parseEventAndPayload(
            $event, $payload
        );

        if ($this->shouldBroadcast($payload)) {
            $this->broadcastEvent($payload[0]);
        }

        $responses = [];

        $listenerSources = [];
        foreach ($this->getListeners($event) as $listener) {
            $listenerSource = '';
            if ($listener instanceof \Closure) {
                $closureObj = new \ReflectionFunction($listener);
                if ($closureObj->getClosureThis() instanceof $this) {
                    $listenerSource = $closureObj->getStaticVariables()['listener'];

                } else {
                    $listenerSource = get_class($closureObj->getClosureThis());
                }
            }

            $listenerSources[] = $listenerSource;
            try {
                $response = $listener($event, $payload);
            }catch (\Exception $exception){
                \Log::order()->error($event . '事件监听者抛出异常:' . $id, $exception);
                throw $exception;
            }
            // If a boolean false is returned from a listener, we will stop propagating
            // the event to any further listeners down in the chain, else we keep on
            // looping through the listeners and firing every one in our sequence.
            if ($response === false) {
                \Log::order()->error($event . '事件监听者返回false:' . $id, $listenerSource);
            }

            $responses[] = $response;

        }

        \Log::order()->info("{$event}事件执行完毕:" . $id, $listenerSources);

        return $responses;
    }

}