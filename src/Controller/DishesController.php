<?php

namespace Controller;

use Core\Config;
use Model\DishesModel;
use TexLab\MyDB\DB;


class DishesController extends AbstractTableController
{

    protected $tableName = "dishes";
    /**
     * @var DishesModel
     */
    private $dishesTable;

    public function __construct($view)
    {
        parent::__construct($view);

        $this->dishesTable = new DishesModel(
            "dishes",
            DB::Link([
                'host' => Config::MYSQL_HOST,
                'username' => Config::MYSQL_USER_NAME,
                'password' => Config::MYSQL_PASSWORD,
                'dbname' => Config::MYSQL_DATABASE
            ])
        );

        $this->view->setFolder('dishes');
    }

    public function actionShow(array $data)
    {
        parent::actionShow($data); // TODO: Change the autogenerated stub
        $this
            ->view
            ->setTemplate('show')
            ->setData([
                'table' => $this
                    ->table
                    ->reset()
                    ->setPageSize(Config::PAGE_SIZE_DISH)
                    ->getPage($data['get']['page'] ?? 1),
                'fields' => array_diff($this->table->getColumnsNames(), ['id']),
                'comments' => $this->table->getColumnsComments(),
                'type' => $this->getClassName(),
                'pageCount' => $this->table->pageCount(),
                'currentPage' => $data['get']['page'] ?? 1
            ]);
    }

    public function actionAdd(array $data)
    {
//        print_r($data['post']);
        //Array ( [post] => Array ( [namedishes] => [statusdish] => Актуально [composition] => [volume] => [unit] => [price] => )
        // [get] => Array ( [action] => add [type] => dishes ) )
        if (empty($data['post']['namedishes'])) {
            $_SESSION['errors'][] = "Ввести название блюда!";
            $this->redirect("?action=show&type=dishes");
        } elseif (empty($data['post']['composition'])) {
            $_SESSION['errors'][] = "Укажите состав!";
            $this->redirect("?action=show&type=dishes");
        } elseif (empty($data['post']['volume'])) {
            $_SESSION['errors'][] = "Ввести необходимое количество порции!";
            $this->redirect("?action=show&type=dishes");
        } elseif (empty($data['post']['unit'])) {
            $_SESSION['errors'][] = "Ввести единицы измерения порции!";
            $this->redirect("?action=show&type=dishes");
        } elseif (empty($data['post']['price'])) {
            $_SESSION['errors'][] = "Ввести цену!";
            $this->redirect("?action=show&type=dishes");
        } elseif (empty($_FILES['imgdishes']['name'])) {
            $_SESSION['errors'][] = "Загрузите изображение!";
            $this->redirect("?action=show&type=dishes");
        } else {
            $data['post']['imgdishes'] = $_FILES['imgdishes']['name'];

            $ext = pathinfo($_FILES['imgdishes']['name'], PATHINFO_EXTENSION);

            $id = $this->table->add($data['post']);

            move_uploaded_file(
                $_FILES['imgdishes']['tmp_name'],
                "images/dishes/$id.$ext"
            );


            $pageCount = $this->table->setPageSize(Config::PAGE_SIZE_DISH)->pageCount();
            $this->redirect("?action=show&type=dishes&page=$pageCount");
        }


    }

    public function actionEdit(array $data)
    {
        $data['post']['imgdishes'] = $_FILES['imgdishes']['name'];
        $ext = pathinfo($_FILES['imgdishes']['name'], PATHINFO_EXTENSION);
        $id = $data['post']['id'];

        move_uploaded_file(
            $_FILES['imgdishes']['tmp_name'],
            "images/dishes/$id.$ext"
        );

        parent::actionEdit($data);
    }

    public function actionDel(array $data)
    {
//        print_r($this->dishesTable->getCountOrdersDish());
//        print_r($data);
        if ($this->dishesTable->getCountOrdersDish($data['get']['id']) == 0) {
            $id = $data['get']['id'];
            $img = $this->table->get(['id' => $id])[0]['imgdishes'];
            $ext = pathinfo($img, PATHINFO_EXTENSION);
            if (file_exists("images/dishes/$id.$ext")) {
                unlink("images/dishes/$id.$ext");
            }

        } else {
            $_SESSION['errors'][] = "Это блюдо уже заказо!";
            $this->redirect("$_SERVER[HTTP_REFERER]");
        }
        parent::actionDel($data);
    }
}
