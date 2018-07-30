<?php

namespace TodoApi;

class TodoItem
{

    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $listId;

    /**
     * @var string
     */
    private $description;

    /**
     * @var int
     */
    private $dueDate;

    /**
     * @var boolean
     */
    private $isCompleted;

    /**
     * TodoItem constructor.
     * @param array|object $todoItem
     */
    public function __construct($todoItem)
    {
        //allow for an object to be passed in.
        $todoItem = is_object($todoItem) ? (array)$todoItem : $todoItem;

        $this->id = $todoItem['id'];
        $this->listId = $todoItem['listId'];
        $this->description = $todoItem['description'];
        $this->dueDate = $todoItem['dueDate'];
        $this->isCompleted = $todoItem['isCompleted'];
    }

    /**
     * @return int
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function listId()
    {
        return $this->listId;
    }

    /**
     * @return string
     */
    public function description()
    {
        return $this->description;
    }

    /**
     * @return int
     */
    public function dueDate()
    {
        return $this->dueDate;
    }

    /**
     * @return bool
     */
    public function isCompleted()
    {
        return $this->isCompleted;
    }

    /**
     * Function to return an array of all available item data.
     * @return array
     */
    public function data(){
        return [
            'id' => $this->id(),
            'listId' => $this->listId(),
            'description' => $this->description(),
            'dueDate' => $this->dueDate(),
            'isCompleted' => $this->isCompleted(),
        ];
    }
}
