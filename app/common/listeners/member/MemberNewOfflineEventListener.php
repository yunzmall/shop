<?php

namespace app\common\listeners\member;

use app\common\events\member\MemberNewOfflineEvent;
use app\common\events\ProfitEvent;

class MemberNewOfflineEventListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  ProfitEvent  $event
     * @return void
     */
    public function handle(MemberNewOfflineEvent $event)
    {
        $parent_id = $event->getParentId();
        $member_id = $event->getUid();
        $is_invite = $event->getIsInvite();
        if ($is_invite) {
            return;
        }
        $memberRelation = new \app\common\services\member\MemberRelation();
        $memberRelation->rewardPoint($parent_id, $member_id);
    }
}