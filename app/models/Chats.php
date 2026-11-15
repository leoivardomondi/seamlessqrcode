<?php
/*
 * Copyright (c) 2025 leoivard
 */

namespace Altum\models;

defined('ALTUMCODE') || die();

class Chats extends Model {

    public function delete($chat_id) {

        if(!$chat = db()->where('chat_id', $chat_id)->getOne('chats', ['user_id', 'chat_id',])) {
            return;
        }

        $result = database()->query("SELECT `image` FROM `chats_messages` WHERE `chat_id` = {$chat->chat_id}");
        while($row = $result->fetch_object()) {
            \Altum\Uploads::delete_uploaded_file($row->image, 'chats_images');
        }

        /* Delete from database */
        db()->where('chat_id', $chat_id)->delete('chats');
    }

}
