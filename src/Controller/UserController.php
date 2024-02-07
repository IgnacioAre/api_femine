<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface; //Encrypt password

/**
 * @Route("/api", name="api_")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/user-login", name="user_login")
     */
    public function userLogin(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $user = new User();

        $document = (isset($_POST['document'])) ? $_POST['document'] : null;
        $password = (isset($_POST['password'])) ? $_POST['password'] : null;

        if($document != null){
            $user = $entityManager->getRepository(User::class)->findBy(['document' => $document]);
        }


        if(!empty($user[0])){

            if (!$passwordHasher->isPasswordValid($user[0], $password)) {
                return $this->json('El documento o contraseña no coincide.', 203, ["Content-Type" => "application/json"]);
            }

            $hashedPassword = $passwordHasher->hashPassword(
                $user[0],
                $password
            );

            $token = $this->generarTokenLogin();

            $user[0]->setTokenLogin($token);

            $entityManager->persist($user[0]);
            $entityManager->flush();

            return $this->json($token, 200, ["Content-Type" => "application/json"]);

        }else{

            return $this->json('Este documento no pertenece a ningún usuario.', 203, ["Content-Type" => "application/json"]);


        }
        

    }


    /**
     * @Route("/user-register", name="user_register")
     */
    public function userRegister(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $user = new User();

        $name = (isset($_POST['name'])) ? $_POST['name'] : null;
        $subname = (isset($_POST['subname'])) ? $_POST['subname'] : null;
        $document = (isset($_POST['document'])) ? $_POST['document'] : null;
        $password = (isset($_POST['password'])) ? $_POST['password'] : null;
        $created_at = new \DateTime();
        $created_at->setTimezone(new \DateTimeZone('GMT-3'));

        if($document == null){
            return $this->json('Error, debe completar el documento y contraseña.', 400, ["Content-Type" => "application/json"]);
        }

        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $password
        );

        $user->setName(strtoupper(trim($name)));
        $user->setSubname(strtoupper(trim($subname)));
        $user->setDocument($document);
        $user->setPassword($hashedPassword);
        $user->setRoles(["USER"]);
        $user->setCreatedAt($created_at);
        $user->setActive(1);

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json($user, 200, ["Content-Type" => "application/json"]);

    }

    /**
     * @Route("/user-update", name="user_register")
     */
    public function userUpdate(EntityManagerInterface $entityManager): JsonResponse
    {

        $name = (isset($_POST['name'])) ? $_POST['name'] : null;
        $subname = (isset($_POST['subname'])) ? $_POST['subname'] : null;
        $id = (isset($_POST['id'])) ? $_POST['id'] : null;

        $user = $entityManager->getRepository(User::class)->find($id);

        if($id == null){
            return $this->json('Error, el id no es válido.', 400, ["Content-Type" => "application/json"]);
        }

        $user->setName(strtoupper(trim($name)));
        $user->setSubname(strtoupper(trim($subname)));

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json($user, 200, ["Content-Type" => "application/json"]);

    }

    private function generarTokenLogin(){

        $texto="AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz0123456789";

        $largo=strlen($texto);

        $letras="";

        $contador=1;

        while($contador<=20){
            $azar=rand(1,$largo);
            $posicion=$azar-1;

            //Substr(Variable Texto,Posicion, Cantidad)

            $caracter=Substr($texto,$posicion,1);

            $letras=$letras.$caracter;

            $contador++;

        }


        return $letras;
    }


    /**
     * @Route("/get-user", name="get_user")
     */
    public function getUserByToken(EntityManagerInterface $entityManager): JsonResponse
    {
        $token = (isset($_POST['token'])) ? $_POST['token'] : null;

        if($token == null){
            return $this->json('Error, debe enviar el token como parámetro.', 400, ["Content-Type" => "application/json"]);
        }


        $data = $entityManager->getRepository(User::class)->findBy(['tokenLogin' => $token]);

        $user = [];

        /**
         * @var $u User
         */

        foreach ($data as $u) {

            $user['document'] = $u->getDocument();
            $user['name'] = $u->getName() . (($u->getSubname()) ? ' ' . $u->getSubname() : '');
            $user['rol'] = $u->getRoles()[0];
        }

        return $this->json($user, 200, ["Content-Type" => "application/json"]);

    }


    /**
     * @Route("/get-users-list", name="get_users_list")
     */
    public function getUsersList(EntityManagerInterface $entityManager): JsonResponse
    {

        $data = $entityManager->getRepository(User::class)->findAll();

        $user = [];

        /**
         * @var $u User
         */

        foreach ($data as $u) {

            $document = $u->getDocument();
            $primeraParte = substr($document,0,1);
            $segundaParte = substr($document,1,3);
            $terceraParte = substr($document,4,3);
            $cuartaParte = substr($document,7,1);

            //Solo almaceno los usuarios que contengan el rol de USER
            if (in_array('USER', $u->getRoles(), true)) {
                $user[] = array(
                    'id' => $u->getId(),
                    'document' => $primeraParte.'.'.$segundaParte.'.'.$terceraParte.'-'.$cuartaParte,
                    'name' => $u->getName(),
                    'subname' => (($u->getSubname()) ? ' ' . $u->getSubname() : '')
                );
            }

        }

        return $this->json($user, 200, ["Content-Type" => "application/json"]);

    }

}
