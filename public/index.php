<?php

use Slim\App;
use TodoApi\Controller\TodoListController;
use TodoApi\Controller\TodoListItemController;
use TodoApi\TodoListRepository;
use TodoApi\TodoListItemRepository;
use TodoApi\database\databaseConnection;

require_once __DIR__ . '/../vendor/autoload.php';
//Parse the settings yaml file - Stored outside web root.
$settings = yaml_parse_file(__DIR__. '/../config/settings.yaml');

$app = new App($settings);
$container = $app->getContainer();

/**
 * Add Database to the app container.
 * @param $c
 * @return databaseConnection
 */
$container[databaseConnection::class] = function($c){
    $connectionInfo = $c['settings']['database'];
    return new databaseConnection($connectionInfo['name'], $connectionInfo['user'], $connectionInfo['pass'], $connectionInfo['host']);
};

$container[TodoListRepository::class] = function() {
    return new TodoListRepository();
};

/**
 * Create List routes
 */
$app->group('/list', function () {
    $this->get('', TodoListController::class . ':getAllTodoLists')->setName('listAll');
    $this->post('', TodoListController::class . ':createTodoList')->setName('createList');
    $this->get('/{id}', TodoListController::class . ':getItemById')->setName('getItemById');
    $this->put('/{id}', TodoListController::class . ':updateTodoList')->setName('updateListById');
    $this->delete('/{id}', TodoListController::class . ':deleteTodoList')->setName('deleteListById');
});


$container[TodoListItemRepository::class] = function() {
    return new TodoListItemRepository();
};

/**
 * Create Item Routes
 */
$app->group('/item', function () {
    $this->get('', TodoListItemController::class . ':getAllTodoItems')->setName('listAllItems');
    $this->post('', TodoListItemController::class . ':createItem')->setName('createItem');
    $this->get('/overdue', TodoListItemController::class . ':getOverdue')->setName('fetchById');
    $this->get('/status/{status}', TodoListItemController::class . ':fetchByStatus')->setName('fetchById');
    $this->get('/{listId}/{id}', TodoListItemController::class . ':fetchById')->setName('fetchById');
    $this->get('/{listId}', TodoListItemController::class . ':fetchByListId')->setName('fetchByListId');
    $this->put('/{id}', TodoListItemController::class . ':updateItemById')->setName('updateItemById');
    $this->delete('/{id}', TodoListItemController::class . ':deleteItemById')->setName('deleteItemById');
});

$app->run();