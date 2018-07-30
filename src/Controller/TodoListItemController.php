<?php

namespace TodoApi\Controller;

use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use TodoApi\TodoListItemRepository;
use TodoApi\database\databaseConnection;

class TodoListItemController
{

    /**
     * @var TodoListItemRepository
     */
    private $todoListItemRepository;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->todoListItemRepository = $container->get(TodoListItemRepository::class);
        $this->database = $container->get(databaseConnection::class);
    }

    /**
     * @param Request $request
     * @param Response $response
     *
     * @return Response
     */
    public function getAllTodoItems(Request $request, Response $response)
    {
        $data = [];
        $items = $this->todoListItemRepository->fetchAll($this->database);
        foreach ($items as $item) {
            $data[] = $item->data();
        }
        return $response->withJson($data);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function fetchByListId(Request $request, Response $response, $args)
    {
        $data = [];
        $items = $this->todoListItemRepository->fetchByListId($this->database, $args['listId']);
        foreach ($items as $item) {
            $data[] = $item->data();
        }
        return $response->withJson($data);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function fetchById(Request $request, Response $response, $args)
    {
        $item = $this->todoListItemRepository->fetchById($this->database, $args['listId'], $args['id']);
        return $response->withJson($item->data());
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function getOverdue(Request $request, Response $response)
    {
        $items = $this->todoListItemRepository->fetchOverdue($this->database);
        foreach ($items as $item) {
            $data[] = $item->data();
        }
        return $response->withJson($data);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function fetchByStatus(Request $request, Response $response, $args)
    {
        $items = $this->todoListItemRepository->fetchByStatus($this->database, $args['status']);
        foreach ($items as $item) {
            $data[] = $item->data();
        }
        return $response->withJson($data);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     */
    public function createItem(Request $request, Response $response){
        $itemData = [];
        $itemData['listId'] = $request->getParam('listId');
        $itemData['description'] = $request->getParam('description');
        $itemData['dueDate'] = $request->getParam('dueDate');
        $itemData['isCompleted'] = $request->getParam('isCompleted') === true ? 1 : 0;
        $item = $this->todoListItemRepository->createItem($this->database, $itemData);
        return $response->withJson($item->data());
    }

    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function updateItemById(Request $request, Response $response, $args){
        $itemData = [];
        $itemData['listId'] = $request->getParam('listId');
        $itemData['description'] = $request->getParam('description');
        $itemData['dueDate'] = $request->getParam('dueDate');
        $itemData['isCompleted'] = $request->getParam('isCompleted') === true ? 1 : 0;
        $item = $this->todoListItemRepository->updateItem($this->database, $itemData, $args['id']);
        return $response->withJson($item->data());
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function deleteItemById(Request $request, Response $response, $args)
    {
        $this->todoListItemRepository->deleteById($this->database, $args['id']);
        return $response->withJson(['success' => 'deleted']);
    }
}
