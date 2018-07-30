<?php

namespace TodoApi;
use TodoApi\database\databaseConnection;

class TodoListRepository
{
    /**
     * @return TodoList[]
     */
    public function fetchAll(databaseConnection $database)
    {
        $lists = [];
        $listResults = $database->lists->select()->execute()->fetchAll();
        foreach($listResults as $result){
            $lists[] = new TodoList($result->id, $result->name);
        }
        return $lists;
    }

    /**
     * @param databaseConnection $database
     * @param $id
     * @return TodoList
     */
    public function fetchById(databaseConnection $database, $id){
        $list = $database->lists->select()->where('id', $id)->execute()->fetchAssoc();
        return new TodoList($list['id'], $list['name']);
    }

    /**
     * Create a new todo list. Return the created list.
     * @param databaseConnection $database
     * @param $name
     */
    public function createList(databaseConnection $database, $name){
        $database->lists->insert(['name'], [$name])->execute();
        $newList = $database->lists->select()->orderBy('id', 'DESC')->execute()->fetchAssoc();
        return new TodoList($newList['id'], $newList['name']);
    }

    /**
     * @param databaseConnection $database
     * @param $name
     * @return TodoList
     */
    public function updateList(databaseConnection $database, $id, $name){
        $database->lists->update(['name'], [$name])->where('id', $id)->execute();
        $newList = $database->lists->select()->where('id', $id)->execute()->fetchAssoc();
        return new TodoList($newList['id'], $newList['name']);
    }

    /**
     * @param databaseConnection $database
     * @param $id
     * @return boolean
     */
    public function deleteById(databaseConnection $database, $id){
        $database->lists->delete()->where('id', $id)->execute();
        $database->listItems->delete()->where('listId', $id)->execute();
        return true;
    }
}
