<?php
namespace App\Controller;

class ArticleController
{
    public function index($id,$name)
    {
        echo 'article index '.$id.' '.$name;
    }


}