<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class UserController extends AbstractController
{
    public function manageUsers() : Response
    {
        return $this->render('admin/management/users/users.html.twig');
    }
}