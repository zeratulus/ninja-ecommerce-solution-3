<?php


class ModelChatChat extends Model
{

    public function install()
    {
        $sql = "CREATE TABLE `".DB_PREFIX."chat` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `title` text DEFAULT NULL,
              `date_added` timestamp NULL DEFAULT NULL,
               PRIMARY KEY (`id`),
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
        $this->getDb()->query($sql);
        
        $sql = "CREATE TABLE ".DB_PREFIX."chat_members (
                id INT UNSIGNED auto_increment NULL,
                foreign_id INT UNSIGNED NULL,
                `type` varchar(255) NULL,
                chat_id INT UNSIGNED NULL,
                date_added TIMESTAMP NULL,
                owner BOOL NULL,
                CONSTRAINT ".DB_PREFIX."chat_members_pk PRIMARY KEY (id)
            )
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

        $this->getDb()->query($sql);
        $this->getDb()->query("CREATE INDEX ".DB_PREFIX."chat_members_foreign_id_IDX USING BTREE ON ".DB_DATABASE.".".DB_PREFIX."chat_members (foreign_id);");
        $this->getDb()->query("CREATE INDEX ".DB_PREFIX."chat_members_chat_id_IDX USING BTREE ON ".DB_DATABASE.".".DB_PREFIX."chat_members (chat_id);");

        $sql = "
            CREATE TABLE ".DB_PREFIX."chat_messages (
                id INT UNSIGNED auto_increment NULL,
                chat_id INT UNSIGNED NULL,
                chat_member_id INT NULL,
                message TEXT NULL,
                date_added TIMESTAMP NULL,
                date_modified TIMESTAMP NULL,
                CONSTRAINT ".DB_PREFIX."chat_messages_pk PRIMARY KEY (id)
            )
            ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

        $this->getDb()->query($sql);
        $this->getDb()->query("CREATE INDEX ".DB_PREFIX."chat_messages_chat_id_IDX USING BTREE ON ".DB_DATABASE.".".DB_PREFIX."chat_messages (chat_id);");
        $this->getDb()->query("CREATE INDEX ".DB_PREFIX."chat_messages_chat_member_id_IDX USING BTREE ON ".DB_DATABASE.".".DB_PREFIX."chat_messages (chat_member_id);");
    }

    public function addChat($data)
    {
        $sql = "INSERT INTO ".DB_PREFIX."chat (title, date_added) VALUES('{$this->getDb()->escape($data['title'])}', '{$data['date_added']}');";
        $this->getDb()->query($sql);
        return $this->getDb()->getLastId();
    }

    function getChatById($chat_id) {
        $sql = "SELECT * FROM ".DB_PREFIX."chat WHERE id = '{$chat_id}'";
        $results = $this->getDb()->query($sql);
        return $results->rows;
    }

    function getChatByMember($member_id, $type) {
        $sql = "SELECT c.*, cm.* FROM ".DB_PREFIX."chat c LEFT JOIN ".DB_PREFIX."chat_members cm ON (c.id = cm.chat_id) WHERE cm.foreign_id = '{$member_id}' AND cm.type = '{$type}'";
        $results = $this->getDb()->query($sql);
        return $results->rows;
    }

    function getChatMembers($chat_id) {
        $sql = "SELECT * FROM ".DB_PREFIX."chat_members WHERE chat_id = '{$chat_id}'";
        $results = $this->getDb()->query($sql);
        return $results->rows;
    }

    function getChatMembersByChatIds($ids) {
        $sql = "SELECT * FROM ".DB_PREFIX."chat_members WHERE chat_id IN ('{$this->getDb()->arrayToInClause($ids)}')";
        $results = $this->getDb()->query($sql);
        return $results->rows;
    }

    function getChatMessages($chat_id) {
        $sql = "SELECT * FROM ".DB_PREFIX."chat_messages WHERE chat_id = '{$chat_id}'";
        $results = $this->getDb()->query($sql);
        return $results->rows;
    }

    function getChatLastMessagesList($ids) {
        $sql = "SELECT DISTINCT id, chat_id, message, chat_member_id, MAX(date_added) FROM ".DB_PREFIX."chat_messages WHERE chat_id IN (" . $this->getDb()->arrayToInClause($ids) . ") GROUP BY chat_id";
        $results = $this->getDb()->query($sql);
        return $results->rows;
    }

    function getChatMessagesFrom($chat_id, $last_message_id) {
        $sql = "SELECT * FROM ".DB_PREFIX."chat_messages WHERE chat_id = '{$chat_id}' AND id > '{$last_message_id}'";
        $results = $this->getDb()->query($sql);
        return $results->rows;
    }

    function addChatMember($data) {
        $sql = "INSERT INTO ".DB_PREFIX."chat_members (foreign_id, `type`, chat_id, date_added, owner) VALUES 
                  ('{$data['foreign_id']}', '{$data['type']}', '{$data['chat_id']}', '{$data['date_added']}', '{$data['owner']}');";
        $this->getDb()->query($sql);
        return $this->getDb()->getLastId();
    }

    function addChatMessage($data) {
        $sql = "INSERT INTO ".DB_PREFIX."chat_messages (chat_id, chat_member_id, message, date_added, date_modified) VALUES
            ('{$data['chat_id']}', '{$data['chat_member_id']}', '{$data['message']}', '{$data['date_added']}', '{$data['date_modified']}');";
        $this->getDb()->query($sql);
        return $this->getDb()->getLastId();
    }


}