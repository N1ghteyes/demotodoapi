<?php

namespace TodoApi\Controller;

use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use TodoApi\TodoListRepository;
use TodoApi\database\databaseConnection;

class TodoListController
{

    /**
     * @var TodoListRepository
     */
    private $todoListRepository;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->todoListRepository = $container->get(TodoListRepository::class);
        $this->database = $container->get(databaseConnection::class);
    }

    /**
     * @param Request $request
     * @param Response $response
     *
     * @return Response
     */
    public function getAllTodoLists(Request $request, Response $response)
    {
        $data = [];
        $items = $this->todoListRepository->fetchAll($this->database);
        foreach ($items as $item) {
            $data[] = [
                'id' => $item->id(),
                'name' => $item->name(),
            ];
        }
        return $response->withJson($data);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     */
    public function createTodoList(Request $request, Response $response){
        $name = $request->getParam('name');
        $list = $this->todoListRepository->createList($this->database, $name);
        return $response->withJson(['id' => $list->id(), 'name' => $list->name()]);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function updateTodoList(Request $request, Response $response, $args){
        $name = $request->getParam('name');
        $list = $this->todoListRepository->updateList($this->database, $args['id'], $name);
        return $response->withJson(['id' => $list->id(), 'name' => $list->name()]);
    }

    /**
     * Get a Todo list by ID
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function getTodoList(Request $request, Response $response, $args)
    {
        $list = $this->todoListRepository->fetchById($this->database, $args['id']);
        return $response->withJson(['id' => $list->id(), 'name' => $list->name()]);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function deleteTodoList(Request $request, Response $response, $args)
    {
        $this->todoListRepository->deleteById($this->database, $args['id']);
        return $response->withJson(['success' => 'deleted']);
    }
}
