<?php

namespace TodoApi;
use TodoApi\database\databaseConnection;

/**
 * Essentially a Model, handle all the raw data communication.
 * Class TodoListItemRepository
 * @package TodoApi
 */
class TodoListItemRepository
{
    /**
     * @return TodoItem[]
     */
    public function fetchAll(databaseConnection $database)
    {
        $items = [];
        $itemResults = $database->listItems->select()->execute()->fetchAll();
        foreach($itemResults as $result){
            $items[] = new TodoItem($result);
        }
        return $items;
    }

    /**
     * @param databaseConnection $database
     * @param $listId
     * @return TodoItem[]
     */
    public function fetchByListId(databaseConnection $database, $listId){
        $items = [];
        $itemResults = $database->listItems->select()->where('listId', $listId)->execute()->fetchAll();
        foreach($itemResults as $result){
            $items[] = new TodoItem($result);
        }
        return $items;
    }

    /**
     * @param databaseConnection $database
     * @param $id
     * @return TodoItem
     */
    public function fetchById(databaseConnection $database, $listId, $id){
        $list = $database->listItems->select()->where('listId', $listId)->where('id', $id)->execute()->fetchAssoc();
        return new TodoItem($list);
    }

    /**
     * Create a new todo list. Return the created list.
     * @param databaseConnection $database
     * @param $name
     */
    public function createItem(databaseConnection $database, $item){
        $database->listItems->insert(array_keys($item), array_values($item))->execute();
        $newList = $database->listItems->select()->orderBy('id', 'DESC')->execute()->fetchAssoc();
        return new TodoItem($newList);
    }

    /**
     * @param databaseConnection $database
     * @param $name
     * @return TodoItem
     */
    public function updateItem(databaseConnection $database, $itemData, $id){
        $database->listItems->update(array_keys($itemData), array_values($itemData))->where('id', $id)->execute();
        $newList = $database->listItems->select()->where('id', $id)->execute()->fetchAssoc();
        return new TodoItem($newList);
    }

    /**
     * @param databaseConnection $database
     * @return TodoItem[]
     */
    public function fetchOverdue(databaseConnection $database){
        $itemResults = $database->listItems->select()->where('dueDate', time(), '<=')->execute()->fetchAll();
        foreach($itemResults as $result){
            $items[] = new TodoItem($result);
        }
        return $items;
    }

    /**
     * @param databaseConnection $database
     * @return TodoItem[]
     */
    public function fetchByStatus(databaseConnection $database, $status){
        $itemResults = $database->listItems->select()->where('isCompleted', $status)->execute()->fetchAll();
        foreach($itemResults as $result){
            $items[] = new TodoItem($result);
        }
        return $items;
    }

    /**
     * @param databaseConnection $database
     * @param $id
     * @return boolean
     */
    public function deleteById(databaseConnection $database, $id){
        $database->listItems->delete()->where('id', $id)->execute();
        return true;
    }
}
