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
        if (is_object($event)) {
            list($payload, $event) = [[$event], get_class($event)];
        }

        $responses = [];

        // If an array is not given to us as the payload, we will turn it into one so
        // we can easily use call_user_func_array on the listeners, passing in the
        // payload to each of them so that they receive each of these arguments.
        if (!is_array($payload)) {
            $payload = [$payload];
        }

        $this->firing[] = $event;

        if (isset($payload[0]) && $payload[0] instanceof ShouldBroadcast) {
            $this->broadcastEvent($payload[0]);
        }

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
                $response = call_user_func_array($listener, $payload);
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

        array_pop($this->firing);

        \Log::order()->info("{$event}事件执行完毕:" . $id, $listenerSources);

        return $responses;
    }

}