<?php

namespace Controller;

use Core\Config;

class DishesController extends AbstractTableController
{

    protected $tableName = "dishes";

    public function __construct($view)
    {
        parent::__construct($view);

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
                'pageCount' => $this->table->pageCount()
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

            $this->redirect('?action=show&type=' . $this->getClassName());
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
        $id = $data['get']['id'];
        $img = $this->table->get(['id' => $id])[0]['imgdishes'];
        $ext = pathinfo($img, PATHINFO_EXTENSION);
        if (file_exists("images/dishes/$id.$ext")) {
            unlink("images/dishes/$id.$ext");
        }

        parent::actionDel($data);
    }
}
