<?php


namespace Controller;

use Core\Config;
use View\View;


class CurrentUserOrdersController extends OrdersController
{
    public function __construct(View $view)
    {
        parent::__construct($view);
        $this
            ->view
            ->setFolder("currentuserorders");
    }



    public function actionShow(array $data)
    {
        // print_r($this->table->getTotalPrice($_SESSION['user']['id']));
        $this
            ->view
            ->setTemplate('show')
            ->setData([
                "user_id" => $_SESSION['user']['id'],
                "usersList" => $this->usersTable->getUsers(),
                "dishesList" => $this->dishesTable->getDishes(),
                "totalPrice" => $this->table->getTotalPrice($_SESSION['user']['id']),
                'table' => $this
                    ->table
                    ->reset()
                    ->setPageSize(Config::PAGE_SIZE)
                    // ->addWhere("`orders`. `users_id` = " . $_SESSION['user']['id'])
                    ->getOrdersPageUserFilter($data['get']['page'] ?? 1, $_SESSION['user']['id']),
                'fields' => array_diff($this->table->getColumnsNames(), ['id']),
                'comments' => $this->table->getColumnsComments(),
                'type' => $this->getClassName(),
                'pageCount' => $this->table->pageCountCurrentUser($_SESSION['user']['id']),
                'currentPage' => $data['get']['page'] ?? 1
            ]);
    }

    public function actionShowEdit(array $data)
    {
        parent::actionShowEdit($data); // TODO: Change the autogenerated stub

        $this
            ->view
            ->setFolder('currentuserorders');
    }

    // добавление заказа c перебросом на последнюю страницу
    public function actionAddOrder(array $data)
    {
//        print_r($data);
//        print_r($_SESSION);
        $users_id = $_SESSION['user']['id'];
        $dishes_id = $data['get']['id'];
        $this->table->getAddOrders($users_id, $dishes_id);

        $pageCount = $this->table->setPageSize(Config::PAGE_SIZE)->pageCountCurrentUser($users_id);
        $this->redirect("?action=show&type=currentuserorders&page=$pageCount");
    }
}
