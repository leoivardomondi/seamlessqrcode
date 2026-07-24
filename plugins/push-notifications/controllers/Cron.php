<?php

/* Process a maximum of 50 push notifications per cron job run */
$i = 1;
while(($push_notification = db()->where('status', 'processing')->getOne('push_notifications')) && $i <= 50) {
    $push_notification->push_subscribers_ids = json_decode($push_notification->push_subscribers_ids ?? '[]');
    $push_notification->sent_push_subscribers_ids = json_decode($push_notification->sent_push_subscribers_ids ?? '[]');

    $push_subscribers_ids_to_be_processed = array_diff($push_notification->push_subscribers_ids, $push_notification->sent_push_subscribers_ids);

    /* Get first user that needs to be processed */
    if(count($push_subscribers_ids_to_be_processed)) {
        $push_subscriber_id = reset($push_subscribers_ids_to_be_processed);
        $push_subscriber = db()->where('push_subscriber_id', $push_subscriber_id)->getOne('push_subscribers');

        $push_notification->sent_push_subscribers_ids[] = $push_subscriber_id;

        /* Prepare the web push */
        $auth = [
            'VAPID' => [
                'subject' => 'mailto:hey@example.com',
                'publicKey' => settings()->push_notifications->public_key,
                'privateKey' => settings()->push_notifications->private_key,
            ],
        ];

        $web_push = new \Minishlink\WebPush\WebPush($auth);

        /* Set subscriber data */
        $subscriber = [
            'endpoint' => $push_subscriber->endpoint,
            'expirationTime' => null,
            'keys' => json_decode($push_subscriber->keys, true)
        ];

        /* Prepare the push data */
        $push_notification->title = process_spintax($push_notification->title);
        $push_notification->description = process_spintax($push_notification->description);

        /* Send the web push */
        $content = [
            'title' => $push_notification->title,
            'description' => $push_notification->description,
            'url' => $push_notification->url,
        ];

        if(settings()->push_notifications->icon) {
            $content['icon'] = \Altum\Uploads::get_full_url('push_notifications_icon') .  settings()->push_notifications->icon;
        }

        if(settings()->push_notifications->icon) {
            $content['badge'] = \Altum\Uploads::get_full_url('push_notifications_icon') .  settings()->push_notifications->icon;
        }

        $report = $web_push->sendOneNotification(
            \Minishlink\WebPush\Subscription::create($subscriber),
            json_encode($content),
            ['TTL' => 5000]
        );

        /* Update the push notification */
        db()->where('push_notification_id', $push_notification->push_notification_id)->update('push_notifications', [
            'sent_push_notifications' => db()->inc(),
            'sent_push_subscribers_ids' => json_encode($push_notification->sent_push_subscribers_ids),
            'status' => count($push_subscribers_ids_to_be_processed) == 1 ? 'sent' : 'processing',
            'last_sent_datetime' => \Altum\Date::$date,
        ]);

        /* Unsubscribe if push failed */
        if($report->getResponse()->getStatusCode() == 410) {
            db()->where('push_subscriber_id', $push_subscriber_id)->delete('push_subscribers');
        } else if($report->getResponse()->getStatusCode() == 410) {

        } else {
            if($push_subscriber->user_id) {
                \Altum\Logger::users($push_subscriber->user_id, 'push_notification.' . $push_notification->push_notification_id . '.sent');
            }
        }

        if(DEBUG) {
            echo '<br />' . "push_notification_id - {$push_notification->push_notification_id} | push_subscriber_id - {$push_subscriber_id} | user_id - {$push_subscriber->user_id} sent web push." . '<br />';
        }
    }

    /* If there are no users to be processed, mark as sent */
    else {
        db()->where('push_notification_id', $push_notification->push_notification_id)->update('push_notifications', [
            'status' => 'sent'
        ]);
    }

    $i++;
}
