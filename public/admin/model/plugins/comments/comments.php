<?php

class ModelPluginsCommentsComments extends Model
{
    private string $table_name = DB_PREFIX . "comments";

    public function isInstalled(): bool
    {
        $sql = "SELECT * FROM information_schema.tables WHERE table_schema = '".DB_DATABASE."' AND table_name = '$this->table_name' LIMIT 1;";
        return (bool)$this->getDb()->query($sql)->num_rows;
    }

    public function install(): void
    {
        $sql = "CREATE TABLE `$this->table_name` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `parent_id` int(11) NOT NULL,
              `customer_id` int(11) NOT NULL,
              `author` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
              `text` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
              `rating` int(1) NOT NULL,
              `status` tinyint(1) NOT NULL DEFAULT 0,
              `date_added` datetime NOT NULL,
              `date_modified` datetime NOT NULL,
              `route` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `avatar` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
              PRIMARY KEY (`id`),
              KEY `parent_id` (`parent_id`),
              KEY `customer_id` (`customer_id`),
              KEY `author` (`author`),
              KEY `route` (`route`)
            ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;        
        ";
        $this->getDb()->query($sql);
    }

    public function addComment(\Plugins\Comment $data)
    {
        $sql = "INSERT INTO {$this->table_name} (`parent_id`,`customer_id`,`author`,`text`,`rating`,`status`,`date_added`,`date_modified`,`route`,`avatar`) VALUES (
            '{$data->getParentId()}',
            '{$data->getCustomerId()}',
            '{$data->getAuthor()}',
            '{$data->getText()}',
            '{$data->getRating()}',
            '{$data->getStatus()}',
            '{$data->getDateAdded()}',
            '{$data->getDateModified()}',
            '{$data->getRoute()}',
            '{$data->getAvatar()}'
        );";
        $this->getDb()->query($sql);
        return $this->getDb()->getLastId();
    }

    public function editComment(\Plugins\Comment $data): int
    {
        $sql = "UPDATE {$this->table_name} SET 
            `parent_id` = '{$data->getParentId()}',
            `customer_id` = '{$data->getCustomerId()}',
            `author` = '{$data->getAuthor()}',
            `text` = '{$data->getText()}',
            `rating` = '{$data->getRating()}',
            `status` = '{$data->getStatus()}',
            `date_added` = '{$data->getDateAdded()}',
            `date_modified` = '{$data->getDateModified()}',
            `route` = '{$data->getRoute()}',
            `avatar` = '{$data->getAvatar()}'
            WHERE id = '{$data->getId()}';
        ";
        $this->getDb()->query($sql);
        return $this->getDb()->countAffected();
    }

    public function deleteComment(int $id): int
    {
        $sql = "DELETE FROM {$this->table_name} WHERE id = `$id`";
        $this->getDb()->query($sql);
        return $this->getDb()->countAffected();
    }

    public function getComments($filter): array
    {
        $sql = "SELECT * FROM {$this->table_name}";
        $sql .= $this->processWhereClause($filter);
        return $this->getDb()->query($sql)->rows;
    }

   public function getCommentsTotal($filter): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table_name}";
        $sql .= $this->processWhereClause($filter);
        return $this->getDb()->query($sql)->row['total'];
    }

    public function getComment(int $id): \Plugins\Comment
    {
        $sql = "SELECT * FROM {$this->table_name} WHERE id = `$id`";
        $data = $this->getDb()->query($sql)->row;
        $comment = new Plugins\Comment($this->registry);
        if (!empty($data)) {
            $comment->mapData($data);
        }
        return $comment;
    }

    public function massStatusChange(array $ids, bool $status): int
    {
        $sql = "UPDATE {$this->table_name} SET status = `$status` WHERE id IN ({$this->getDb()->arrayToInClause($ids)})";
        $this->getDb()->query($sql);
        return $this->getDb()->countAffected();
    }

    public function processWhereClause(array $filter): string
    {
        $sql = '';
        $where = [];
        if (!empty($filter['filter_route'])) {
            $where[] = " (route = `{$filter['filter_route']}` OR route LIKE `%{$filter['filter_route']}%`)";
        }
        if (!empty($filter['filter_author'])) {
            $where[] = " author LIKE `%{$filter['filter_author']}%`";
        }
        if (!empty($filter['filter_status'])) {
            $where[] = " status = `{$filter['filter_status']}`";
        }
        if (!empty($filter['filter_date_added'])) {
            $where[] = " date_added = `{$filter['date_added']}`";
        }
        if (!empty($where)) {
            $sql = " WHERE " . implode(' AND ', $where);
        }

        $sort_data = array(
            'route',
            'author',
            'status',
            'date_added'
        );

        if (isset($filter['sort']) && in_array($filter['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $filter['sort'];
        } else {
            $sql .= " ORDER BY date_added";
        }

        if (isset($filter['order']) && ($filter['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($filter['start']) || isset($filter['limit'])) {
            if ($filter['start'] < 0) {
                $filter['start'] = 0;
            }

            if ($filter['limit'] < 1) {
                $filter['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$filter['start'] . "," . (int)$filter['limit'];
        }

        return $sql;
    }

}